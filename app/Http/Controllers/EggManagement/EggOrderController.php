<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\EggCustomer;
use App\Models\EggModule\EggInventory;
use App\Models\EggModule\EggOrder;
use App\Models\EggModule\EggOrderItem;
use App\Services\EggModule\EggInventoryReservationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EggOrderController extends Controller
{
    public function __construct(
        private EggInventoryReservationService $reservationService
    ) {
    }

    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $query = EggOrder::with(['category', 'customer'])
            ->when($searchQuery, function ($q, $searchQuery) {
                $q->where(function ($sub) use ($searchQuery) {
                    $sub->where('customer_name', 'like', "%{$searchQuery}%")
                        ->orWhere('customer_email', 'like', "%{$searchQuery}%")
                        ->orWhere('customer_phone', 'like', "%{$searchQuery}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($searchQuery) {
                            $customerQuery->where('name', 'like', "%{$searchQuery}%")
                                ->orWhere('portal_code', 'like', "%{$searchQuery}%");
                        });
                });
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('order_date', 'desc')->paginate(15);

        return response()->json($orders);
    }

    public function pendingOrders()
    {
        $orders = EggOrder::whereIn('status', ['pending', 'approved', 'picked'])
            ->with(['category', 'customer', 'shipping'])
            ->orderBy('order_date', 'asc')
            ->get();

        return response()->json($orders);
    }

    public function forSeparation()
    {
        $orders = EggOrder::where('status', 'approved')
            ->with(['category', 'customer'])
            ->orderBy('order_date', 'asc')
            ->get();

        return response()->json($orders);
    }

    public function calendarEvents(Request $request)
    {
        $request->validate([
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);

        $statusColors = [
            'pending' => '#e5a54b',
            'approved' => '#36a4d9',
            'picked' => '#3b7ddd',
            'shipped' => '#1cbb8c',
            'canceled' => '#adb5bd',
        ];

        $query = EggOrder::with('category', 'customer')
            ->whereNotNull('expected_delivery_date')
            ->orderBy('expected_delivery_date');

        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('expected_delivery_date', [
                Carbon::parse($request->start)->startOfDay(),
                Carbon::parse($request->end)->endOfDay(),
            ]);
        }

        $events = $query->get()->map(function (EggOrder $order) use ($statusColors) {
            $customer = $order->customer_name ?? 'Cliente';
            $category = $order->category?->name ?? '';
            $isToday = $order->expected_delivery_date?->isToday();
            $color = $isToday ? '#1cbb8c' : ($statusColors[$order->status] ?? '#3b7ddd');

            return [
                'id' => $order->id,
                'title' => $customer . ' — ' . ($category ?: 'Pedido #' . $order->id),
                'start' => $order->expected_delivery_date->format('Y-m-d'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'customer' => $customer,
                    'category' => $category,
                    'quantity' => $order->quantity_dozens,
                    'status' => $order->status,
                    'order_date' => $order->order_date?->format('Y-m-d'),
                    'expected_delivery_date' => $order->expected_delivery_date->format('Y-m-d'),
                    'unit_price' => $order->unit_price,
                ],
            ];
        });

        return response()->json($events);
    }

    public function show(EggOrder $eggOrder)
    {
        return response()->json($eggOrder->load([
            'category',
            'shipping',
            'customer',
            'items.inventory.egg.category',
            'items.inventory.house',
        ]));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:egg_customers,id',
            'customer_name' => 'required_without:customer_id|string|max:100',
            'customer_tax_id' => 'nullable|string|max:18',
            'customer_email' => 'nullable|email|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'category_id' => 'required|exists:egg_categories,id',
            'quantity_dozens' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string',
        ]);

        if (!empty($validated['customer_id'])) {
            $customer = EggCustomer::findOrFail($validated['customer_id']);
            $validated['customer_name'] = $customer->name;
            $validated['customer_tax_id'] = $customer->tax_id;
            $validated['customer_email'] = $customer->email;
            $validated['customer_phone'] = $customer->phone;
        }

        $validated['status'] = 'pending';
        $order = EggOrder::create($validated);

        return response()->json($order->load(['category', 'customer']), 201);
    }

    public function approve(EggOrder $eggOrder)
    {
        if ($eggOrder->status !== 'pending') {
            return response()->json(['message' => 'Só pedidos pendentes podem ser aprovados.'], 422);
        }

        $eggOrder->update(['status' => 'approved']);

        return response()->json($eggOrder);
    }

    public function pick(Request $request, EggOrder $eggOrder)
    {
        $validated = $request->validate([
            'allocations' => 'required|array|min:1',
            'allocations.*.inventory_id' => 'required|exists:egg_inventories,id',
            'allocations.*.quantity' => 'required|integer|min:1',
        ]);

        if ($eggOrder->status !== 'approved') {
            return response()->json(['message' => 'Só pedidos aprovados podem ser separados.'], 422);
        }

        $eggsNeeded = (int) $eggOrder->quantity_dozens;
        $allocations = collect($validated['allocations'])->map(fn ($row) => [
            'inventory_id' => (int) $row['inventory_id'],
            'quantity' => (int) $row['quantity'],
        ]);

        $allocatedTotal = $allocations->sum('quantity');
        if ($allocatedTotal !== $eggsNeeded) {
            return response()->json([
                'message' => "A soma das quantidades ({$allocatedTotal}) deve ser igual ao pedido ({$eggsNeeded} ovos).",
            ], 422);
        }

        if ($allocations->pluck('inventory_id')->unique()->count() !== $allocations->count()) {
            return response()->json(['message' => 'Não pode usar o mesmo stock mais do que uma vez.'], 422);
        }

        try {
            $order = DB::transaction(function () use ($eggOrder, $allocations) {
                $order = EggOrder::lockForUpdate()->findOrFail($eggOrder->id);
                $createdItems = [];

                foreach ($allocations as $allocation) {
                    $inventory = EggInventory::lockForUpdate()->findOrFail($allocation['inventory_id']);
                    $reserved = $this->reservationService->reserveQuantity($inventory, $allocation['quantity']);

                    $createdItems[] = EggOrderItem::create([
                        'order_id' => $order->id,
                        'inventory_id' => $reserved->id,
                        'quantity' => $allocation['quantity'],
                    ]);
                }

                $order->update(['status' => 'picked']);

                return $order->load('items.inventory.egg.category');
            });
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($order);
    }

    public function cancel(EggOrder $eggOrder)
    {
        if (in_array($eggOrder->status, ['shipped', 'canceled'], true)) {
            return response()->json(['message' => 'Este pedido não pode ser cancelado.'], 422);
        }

        if ($eggOrder->shipping()->exists()) {
            return response()->json(['message' => 'Remova a expedição antes de cancelar o pedido.'], 422);
        }

        DB::transaction(function () use ($eggOrder) {
            $order = EggOrder::lockForUpdate()->with('items.inventory')->findOrFail($eggOrder->id);

            foreach ($order->items as $item) {
                if ($item->inventory) {
                    $this->reservationService->releaseReserved($item->inventory);
                }
                $item->delete();
            }

            $order->update(['status' => 'canceled']);
        });

        return response()->json($eggOrder->fresh());
    }

    public function update(Request $request, EggOrder $eggOrder)
    {
        if (in_array($eggOrder->status, ['picked', 'shipped'], true)) {
            return response()->json(['message' => 'Não pode editar pedidos já separados ou expedidos.'], 422);
        }

        $validated = $request->validate([
            'customer_name' => 'string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'expected_delivery_date' => 'nullable|date',
            'quantity_dozens' => 'integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string',
        ]);

        $eggOrder->update($validated);

        return response()->json($eggOrder);
    }

    public function destroy(EggOrder $eggOrder)
    {
        if ($eggOrder->shipping()->exists()) {
            return response()->json(['message' => 'Remova a expedição antes de apagar o pedido.'], 422);
        }

        DB::transaction(function () use ($eggOrder) {
            $order = EggOrder::with('items.inventory')->findOrFail($eggOrder->id);

            foreach ($order->items as $item) {
                if ($item->inventory && $item->inventory->status === 'reserved') {
                    $this->reservationService->releaseReserved($item->inventory);
                }
                $item->delete();
            }

            $order->delete();
        });

        return response()->json(['message' => 'Order deleted successfully']);
    }

    public function generateInvoice(EggOrder $eggOrder)
    {
        $invoice = [
            'order' => $eggOrder->load('category'),
            'total_value' => $eggOrder->quantity_dozens * $eggOrder->unit_price,
            'generated_at' => now(),
        ];

        return response()->json($invoice);
    }
}
