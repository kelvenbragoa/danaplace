<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\EggInventory;
use App\Models\EggModule\EggOrder;
use App\Models\EggModule\EggShipping;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EggShippingController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $query = EggShipping::with('order.category', 'inventory.egg', 'responsible')
            ->when($searchQuery, function ($q, $searchQuery) {
                $q->where(function ($sub) use ($searchQuery) {
                    $sub->where('invoice_number', 'like', "%{$searchQuery}%")
                        ->orWhere('delivery_note_number', 'like', "%{$searchQuery}%")
                        ->orWhere('delivered_to', 'like', "%{$searchQuery}%")
                        ->orWhere('carrier', 'like', "%{$searchQuery}%")
                        ->orWhere('driver_name', 'like', "%{$searchQuery}%")
                        ->orWhere('vehicle_plate', 'like', "%{$searchQuery}%")
                        ->orWhereHas('order', function ($orderQuery) use ($searchQuery) {
                            $orderQuery->where('customer_name', 'like', "%{$searchQuery}%");
                        });
                });
            });

        if ($request->filled('start_date')) {
            $query->where('shipping_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('shipping_date', '<=', $request->end_date);
        }

        $shippings = $query->orderBy('shipping_date', 'desc')->paginate(15);

        return response()->json($shippings);
    }

    public function todayShipping()
    {
        $shippings = EggShipping::whereDate('shipping_date', Carbon::today())
            ->with('order', 'order.category')
            ->get();

        return response()->json($shippings);
    }

    public function calendarEvents(Request $request)
    {
        $request->validate([
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);

        $query = EggShipping::with('order.category')
            ->orderBy('shipping_date');

        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('shipping_date', [
                Carbon::parse($request->start)->startOfDay(),
                Carbon::parse($request->end)->endOfDay(),
            ]);
        }

        $events = $query->get()->map(function (EggShipping $shipping) {
            $customer = $shipping->order?->customer_name ?? 'Cliente';
            $category = $shipping->order?->category?->name ?? '';
            $isToday = $shipping->shipping_date?->isToday();

            return [
                'id' => $shipping->id,
                'title' => $customer . ' — ' . $shipping->invoice_number,
                'start' => $shipping->shipping_date->format('Y-m-d'),
                'backgroundColor' => $isToday ? '#1cbb8c' : '#3b7ddd',
                'borderColor' => $isToday ? '#1cbb8c' : '#3b7ddd',
                'extendedProps' => [
                    'customer' => $customer,
                    'invoice' => $shipping->invoice_number,
                    'carrier' => $shipping->carrier,
                    'driver' => $shipping->driver_name,
                    'plate' => $shipping->vehicle_plate,
                    'category' => $category,
                    'quantity' => $shipping->order?->quantity_dozens,
                    'shipping_date' => $shipping->shipping_date->format('Y-m-d'),
                ],
            ];
        });

        return response()->json($events);
    }

    public function show(EggShipping $eggShipping)
    {
        return response()->json($eggShipping->load(
            'order.category',
            'inventory.egg.category',
            'inventory.house',
            'responsible'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:egg_orders,id',
            'inventory_id' => 'required|exists:egg_inventories,id',
            'shipping_date' => 'required|date',
            'invoice_number' => 'required|string|max:50|unique:egg_shippings,invoice_number',
            'carrier' => 'required|string|max:100',
            'vehicle_plate' => 'required|string|max:10',
            'driver_name' => 'required|string|max:100',
            'vehicle_temperature' => 'nullable|numeric',
            'seal_number' => 'nullable|string|max:50',
            'health_certificate' => 'nullable|string|max:100',
        ]);

        try {
            $shipping = DB::transaction(function () use ($validated) {
                $order = EggOrder::lockForUpdate()->findOrFail($validated['order_id']);
                $inventory = EggInventory::lockForUpdate()->findOrFail($validated['inventory_id']);

                // quantity_dozens no pedido = quantidade unitária de ovos (1 = 1 ovo)
                $eggsNeeded = (int) $order->quantity_dozens;

                if ($eggsNeeded < 1) {
                    throw new \RuntimeException('O pedido não tem quantidade válida de ovos.');
                }

                if ($inventory->status !== 'available' || $inventory->quantity < 1) {
                    throw new \RuntimeException('Este estoque não está disponível.');
                }

                if ($inventory->quantity < $eggsNeeded) {
                    throw new \RuntimeException(
                        "Estoque insuficiente. Necessário: {$eggsNeeded} ovos. Disponível: {$inventory->quantity}."
                    );
                }

                $inventory->quantity -= $eggsNeeded;
                $inventory->status = $inventory->quantity === 0 ? 'reserved' : 'available';
                if ($inventory->quantity > 0) {
                    $inventory->exit_date = null;
                }
                $inventory->save();

                $validated['quantity_eggs'] = $eggsNeeded;
                $validated['responsible_id'] = auth()->id();

                return EggShipping::create($validated);
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($shipping->load('order.category', 'inventory.egg'), 201);
    }

    public function dispatch(Request $request, EggShipping $eggShipping)
    {
        if ($eggShipping->delivered_at) {
            return response()->json([
                'message' => 'Esta expedição já foi despachada.',
            ], 422);
        }

        $validated = $request->validate([
            'delivery_note_number' => 'nullable|string|max:50',
            'delivered_to' => 'required|string|max:100',
            'delivered_at' => 'required|date',
        ]);

        $deliveredAt = Carbon::parse($validated['delivered_at']);

        $eggShipping->update([
            'delivery_note_number' => $validated['delivery_note_number'] ?? null,
            'delivered_to' => $validated['delivered_to'],
            'delivered_at' => $deliveredAt,
            'shipping_date' => $deliveredAt->toDateString(),
        ]);

        // Só marca o lote como shipped quando já não resta quantidade
        if ($eggShipping->inventory && $eggShipping->inventory->quantity <= 0) {
            $eggShipping->inventory->update([
                'status' => 'shipped',
                'exit_date' => $deliveredAt->toDateString(),
            ]);
        }

        if ($eggShipping->order) {
            $eggShipping->order->update(['status' => 'shipped']);
        }

        return response()->json($eggShipping->load('order.category', 'inventory.egg'));
    }

    public function validateTemperature(Request $request)
    {
        $request->validate([
            'temperature' => 'required|numeric',
            'min_temperature' => 'numeric',
            'max_temperature' => 'numeric',
        ]);

        $minTemp = $request->min_temperature ?? 4;
        $maxTemp = $request->max_temperature ?? 8;

        $isValid = $request->temperature >= $minTemp && $request->temperature <= $maxTemp;

        return response()->json([
            'valid' => $isValid,
            'temperature' => $request->temperature,
            'message' => $isValid ? 'Temperatura dentro do intervalo permitido' : 'Temperatura fora do intervalo permitido',
        ]);
    }

    public function update(Request $request, EggShipping $eggShipping)
    {
        $validated = $request->validate([
            'carrier' => 'string|max:100',
            'vehicle_plate' => 'string|max:10',
            'driver_name' => 'string|max:100',
            'vehicle_temperature' => 'nullable|numeric',
            'seal_number' => 'nullable|string|max:50',
            'health_certificate' => 'nullable|string|max:100',
            'delivery_note_number' => 'nullable|string|max:50',
            'delivered_to' => 'nullable|string|max:100',
            'delivered_at' => 'nullable|date',
        ]);

        $eggShipping->update($validated);

        return response()->json($eggShipping->load('order.category', 'inventory.egg'));
    }

    public function destroy(EggShipping $eggShipping)
    {
        DB::transaction(function () use ($eggShipping) {
            $inventory = EggInventory::lockForUpdate()->find($eggShipping->inventory_id);

            if ($inventory) {
                $eggsToRestore = $eggShipping->quantity_eggs
                    ?? (int) ($eggShipping->order?->quantity_dozens ?? 0);

                if ($eggsToRestore > 0) {
                    $inventory->quantity += $eggsToRestore;
                }

                $inventory->status = 'available';
                $inventory->exit_date = null;
                $inventory->save();
            }

            $order = $eggShipping->order;
            if ($order && $order->status !== 'canceled') {
                $order->update(['status' => 'picked']);
            }

            $eggShipping->delete();
        });

        return response()->json(['message' => 'Shipping record deleted successfully']);
    }

    public function printInvoice(EggShipping $eggShipping)
    {
        $invoice = [
            'shipping' => $eggShipping->load('order', 'order.category', 'responsible', 'inventory.egg'),
            'company' => [
                'name' => 'M+D - InoGest',
                'tax_id' => '123456789',
                'address' => 'Your Company Address',
            ],
            'printed_at' => now(),
        ];

        return response()->json($invoice);
    }
}
