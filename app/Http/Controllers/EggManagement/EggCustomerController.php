<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\EggCustomer;
use Illuminate\Http\Request;

class EggCustomerController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $customers = EggCustomer::query()
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where(function ($sub) use ($searchQuery) {
                    $sub->where('name', 'like', "%{$searchQuery}%")
                        ->orWhere('email', 'like', "%{$searchQuery}%")
                        ->orWhere('phone', 'like', "%{$searchQuery}%")
                        ->orWhere('portal_code', 'like', "%{$searchQuery}%")
                        ->orWhere('tax_id', 'like', "%{$searchQuery}%");
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return response()->json($customers);
    }

    public function getAll()
    {
        $customers = EggCustomer::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'tax_id', 'email', 'phone', 'address', 'portal_code']);

        return response()->json($customers);
    }

    public function show(EggCustomer $eggCustomer)
    {
        return response()->json($eggCustomer->loadCount('orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'tax_id' => 'nullable|string|max:18',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $customer = EggCustomer::create($validated);

        return response()->json($customer, 201);
    }

    public function update(Request $request, EggCustomer $eggCustomer)
    {
        $validated = $request->validate([
            'name' => 'string|max:100',
            'tax_id' => 'nullable|string|max:18',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $eggCustomer->update($validated);

        return response()->json($eggCustomer);
    }

    public function destroy(EggCustomer $eggCustomer)
    {
        if ($eggCustomer->orders()->exists()) {
            return response()->json([
                'message' => 'Não é possível apagar um cliente com pedidos associados.',
            ], 422);
        }

        $eggCustomer->delete();

        return response()->json(['message' => 'Cliente apagado com sucesso']);
    }

    public function regeneratePortalCode(EggCustomer $eggCustomer)
    {
        $code = $eggCustomer->regeneratePortalCode();

        return response()->json([
            'portal_code' => $code,
            'customer' => $eggCustomer,
        ]);
    }
}
