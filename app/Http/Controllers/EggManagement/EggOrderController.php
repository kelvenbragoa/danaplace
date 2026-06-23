<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\EggCustomer;
use App\Models\EggModule\EggOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EggOrderController extends Controller
{
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
        return response()->json($eggOrder->load('category', 'shipping', 'customer'));
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
            'observations' => 'nullable|string',
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
            'generated_at' => now(),
        ];

        return response()->json($invoice);
    }
}
