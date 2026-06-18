<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\Lineage;
use Illuminate\Http\Request;

class LineageController extends Controller
{
    public function index()
    {
        $searchQuery = request('query');

        $lineages = Lineage::query()
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('name', 'like', "%{$searchQuery}%")
                        ->orWhere('supplier', 'like', "%{$searchQuery}%");
                });
            })
            ->withCount('flocks')
            ->orderBy('name')
            ->paginate(15);

        return response()->json($lineages);
    }

    public function getAll()
    {
        $lineages = Lineage::where('is_active', true)->orderBy('name')->get();
        return response()->json($lineages);
    }

    public function show(Lineage $lineage)
    {
        return response()->json($lineage->load('flocks.house'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'supplier' => 'required|string|max:100',
            'production_days' => 'integer|min:0',
            'is_active' => 'boolean'
        ]);

        $lineage = Lineage::create($validated);
        return response()->json($lineage, 201);
    }

    public function update(Request $request, Lineage $lineage)
    {
        $validated = $request->validate([
            'name' => 'string|max:50',
            'supplier' => 'string|max:100',
            'production_days' => 'integer|min:0',
            'is_active' => 'boolean'
        ]);

        $lineage->update($validated);
        return response()->json($lineage);
    }

    public function destroy(Lineage $lineage)
    {
        $lineage->delete();
        return response()->json(['message' => 'Lineage deleted successfully']);
    }

    public function toggleStatus(Lineage $lineage)
    {
        $lineage->update(['is_active' => !$lineage->is_active]);
        return response()->json($lineage);
    }
}
