<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\EggInventory;
use App\Models\EggModule\EggOrder;
use App\Models\EggModule\EggShipping;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EggShippingController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $query = EggShipping::with('order.category', 'inventory.egg', 'responsible')
            ->when($searchQuery, function ($q, $searchQuery) {
                $q->where(function ($sub) use ($searchQuery) {
                    $sub->where('invoice_number', 'like', "%{$searchQuery}%")
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

        $validated['responsible_id'] = auth()->id();
        $shipping = EggShipping::create($validated);

        EggInventory::find($validated['inventory_id'])
            ->update(['status' => 'shipped', 'exit_date' => $validated['shipping_date']]);

        EggOrder::find($validated['order_id'])
            ->update(['status' => 'shipped']);

        return response()->json($shipping->load('order.category', 'inventory.egg'), 201);
    }

    public function dispatch(EggShipping $eggShipping)
    {
        $eggShipping->update(['shipping_date' => Carbon::today()]);

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
        ]);

        $eggShipping->update($validated);

        return response()->json($eggShipping->load('order.category', 'inventory.egg'));
    }

    public function destroy(EggShipping $eggShipping)
    {
        $inventory = $eggShipping->inventory;
        if ($inventory) {
            $inventory->update(['status' => 'available', 'exit_date' => null]);
        }

        $order = $eggShipping->order;
        if ($order) {
            $order->update(['status' => 'picked']);
        }

        $eggShipping->delete();

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
