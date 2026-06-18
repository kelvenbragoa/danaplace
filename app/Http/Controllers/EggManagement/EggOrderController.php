<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\EggOrder;
use Illuminate\Http\Request;

class EggOrderController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $query = EggOrder::with('category')
            ->when($searchQuery, function ($q, $searchQuery) {
                $q->where(function ($sub) use ($searchQuery) {
                    $sub->where('customer_name', 'like', "%{$searchQuery}%")
                        ->orWhere('customer_email', 'like', "%{$searchQuery}%")
                        ->orWhere('customer_phone', 'like', "%{$searchQuery}%");
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
            ->with('category')
            ->orderBy('order_date', 'asc')
            ->get();
        
        return response()->json($orders);
    }

    public function show(EggOrder $eggOrder)
    {
        return response()->json($eggOrder->load('category', 'shipping'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_tax_id' => 'nullable|string|max:18',
            'customer_email' => 'nullable|email|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'category_id' => 'required|exists:egg_categories,id',
            'quantity_dozens' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string'
        ]);

        $validated['status'] = 'pending';
        $order = EggOrder::create($validated);
        
        return response()->json($order->load('category'), 201);
    }

    public function approve(EggOrder $eggOrder)
    {
        $eggOrder->update(['status' => 'approved']);
        return response()->json($eggOrder);
    }

    public function pick(EggOrder $eggOrder)
    {
        $eggOrder->update(['status' => 'picked']);
        return response()->json($eggOrder);
    }

    public function cancel(EggOrder $eggOrder)
    {
        $eggOrder->update(['status' => 'canceled']);
        return response()->json($eggOrder);
    }

    public function update(Request $request, EggOrder $eggOrder)
    {
        $validated = $request->validate([
            'customer_name' => 'string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'expected_delivery_date' => 'nullable|date',
            'quantity_dozens' => 'integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string'
        ]);

        $eggOrder->update($validated);
        return response()->json($eggOrder);
    }

    public function destroy(EggOrder $eggOrder)
    {
        $eggOrder->delete();
        return response()->json(['message' => 'Order deleted successfully']);
    }

    public function generateInvoice(EggOrder $eggOrder)
    {
        $invoice = [
            'order' => $eggOrder->load('category'),
            'total_value' => $eggOrder->quantity_dozens * $eggOrder->unit_price,
            'generated_at' => now()
        ];
        
        return response()->json($invoice);
    }
}
