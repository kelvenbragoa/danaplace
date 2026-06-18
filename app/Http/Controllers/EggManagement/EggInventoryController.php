<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\EggInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EggInventoryController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $query = EggInventory::with('egg.category', 'egg.flock', 'house.farm')
            ->when($searchQuery, function ($q, $searchQuery) {
                $q->where(function ($sub) use ($searchQuery) {
                    $sub->where('location', 'like', "%{$searchQuery}%")
                        ->orWhereHas('egg', function ($eggQuery) use ($searchQuery) {
                            $eggQuery->where('traceability_code', 'like', "%{$searchQuery}%");
                        })
                        ->orWhereHas('house', function ($houseQuery) use ($searchQuery) {
                            $houseQuery->where('name', 'like', "%{$searchQuery}%");
                        });
                });
            });

        if ($request->filled('house_id')) {
            $query->where('house_id', $request->house_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $inventory = $query->orderBy('entry_date', 'desc')->paginate(15);

        return response()->json($inventory);
    }

    public function fifoList()
    {
        $inventory = EggInventory::where('status', 'available')
            ->with('egg', 'egg.category')
            ->orderBy('entry_date', 'asc')
            ->get();
        
        return response()->json($inventory);
    }

    public function getByCategory($categoryId)
    {
        $inventory = EggInventory::whereHas('egg', function($query) use ($categoryId) {
            $query->where('category_id', $categoryId);
        })->where('status', 'available')
          ->with('egg')
          ->orderBy('entry_date', 'asc')
          ->get();
        
        return response()->json($inventory);
    }

    public function stockAlerts()
    {
        $alerts = [];
        
        // Low stock alert (less than 1000 eggs)
        $lowStock = EggInventory::where('status', 'available')
            ->with('egg.category')
            ->get()
            ->groupBy('egg.category_id')
            ->map(function($items) {
                return $items->sum('quantity');
            })
            ->filter(function($quantity) {
                return $quantity < 1000;
            });
        
        if ($lowStock->count() > 0) {
            $alerts['low_stock'] = $lowStock;
        }
        
        // Expiring soon (eggs older than 21 days)
        $expiringSoon = EggInventory::where('status', 'available')
            ->whereHas('egg', function($query) {
                $query->where('lay_date', '<=', Carbon::now()->subDays(21));
            })
            ->with('egg')
            ->get();
        
        if ($expiringSoon->count() > 0) {
            $alerts['expiring_soon'] = $expiringSoon;
        }
        
        return response()->json($alerts);
    }

    public function show(EggInventory $eggInventory)
    {
        return response()->json($eggInventory->load('egg.category', 'egg.flock', 'house.farm'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'egg_id' => 'required|exists:eggs,id',
            'house_id' => 'required|exists:houses,id',
            'quantity' => 'required|integer|min:1',
            'entry_date' => 'required|date',
            'location' => 'nullable|string|max:100',
        ]);

        $validated['status'] = 'available';
        $inventory = EggInventory::create($validated);

        return response()->json($inventory->load('egg.category', 'house'), 201);
    }

    public function reserve(EggInventory $eggInventory, Request $request)
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:' . $eggInventory->quantity]);
        
        if ($request->quantity == $eggInventory->quantity) {
            $eggInventory->update(['status' => 'reserved']);
        } else {
            // Create a new record for the reserved portion
            $reserved = $eggInventory->replicate();
            $reserved->quantity = $request->quantity;
            $reserved->status = 'reserved';
            $reserved->save();
            
            // Reduce original quantity
            $eggInventory->quantity -= $request->quantity;
            $eggInventory->save();
        }
        
        return response()->json($eggInventory);
    }

    public function release(EggInventory $eggInventory)
    {
        $eggInventory->update(['status' => 'available']);
        return response()->json($eggInventory);
    }

    public function update(Request $request, EggInventory $eggInventory)
    {
        $validated = $request->validate([
            'house_id' => 'exists:houses,id',
            'quantity' => 'integer|min:1',
            'entry_date' => 'date',
            'location' => 'nullable|string|max:100',
            'status' => 'in:available,reserved,shipped',
        ]);

        $eggInventory->update($validated);

        return response()->json($eggInventory->load('egg.category', 'house'));
    }

    public function destroy(EggInventory $eggInventory)
    {
        $eggInventory->delete();
        return response()->json(['message' => 'Inventory record deleted successfully']);
    }
}
