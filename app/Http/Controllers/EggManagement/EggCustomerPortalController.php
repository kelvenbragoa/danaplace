<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\EggCategory;
use App\Models\EggModule\EggCustomer;
use App\Models\EggModule\EggOrder;
use Illuminate\Http\Request;

class EggCustomerPortalController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'portal_code' => 'required|string|max:20',
        ]);

        $code = trim($validated['portal_code']);

        $customer = EggCustomer::where('is_active', true)
            ->where(function ($query) use ($code) {
                $query->where('portal_code', strtoupper($code));

                if (preg_match('/^\d+$/', $code)) {
                    $query->orWhere('id', (int) $code);
                }
            })
            ->first();

        if (!$customer) {
            return response()->json([
                'message' => 'Código de acesso inválido ou cliente inativo.',
            ], 401);
        }

        $request->session()->put('egg_customer_id', $customer->id);

        return response()->json([
            'customer' => $customer->only(['id', 'name', 'email', 'phone', 'portal_code']),
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('egg_customer_id');

        return response()->json(['message' => 'Sessão terminada.']);
    }

    public function me(Request $request)
    {
        $customer = $this->customerFromSession($request);

        return response()->json($customer->only(['id', 'name', 'email', 'phone', 'tax_id', 'address', 'portal_code']));
    }

    public function categories()
    {
        $categories = EggCategory::where('is_active', true)->orderBy('min_weight')->get();

        return response()->json($categories);
    }

    public function orders(Request $request)
    {
        $customer = $this->customerFromSession($request);

        $orders = EggOrder::where('customer_id', $customer->id)
            ->with('category')
            ->orderBy('order_date', 'desc')
            ->paginate(15);

        return response()->json($orders);
    }

    public function storeOrder(Request $request)
    {
        $customer = $this->customerFromSession($request);

        $validated = $request->validate([
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'category_id' => 'required|exists:egg_categories,id',
            'quantity_dozens' => 'required|integer|min:1',
            'observations' => 'nullable|string',
        ]);

        $order = EggOrder::create([
            ...$validated,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_tax_id' => $customer->tax_id,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'status' => 'pending',
        ]);

        return response()->json($order->load('category'), 201);
    }

    private function customerFromSession(Request $request): EggCustomer
    {
        $customerId = $request->session()->get('egg_customer_id');

        if (!$customerId) {
            abort(401, 'Sessão do portal expirada. Introduza o código de acesso novamente.');
        }

        $customer = EggCustomer::where('id', $customerId)->where('is_active', true)->first();

        if (!$customer) {
            $request->session()->forget('egg_customer_id');
            abort(401, 'Cliente não encontrado ou inativo.');
        }

        return $customer;
    }
}
