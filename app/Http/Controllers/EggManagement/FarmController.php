<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\Farm;
use Illuminate\Http\Request;

class FarmController extends Controller
{
    public function index()
    {
        $searchQuery = request('query');

        $farms = Farm::query()
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('name', 'like', "%{$searchQuery}%")
                        ->orWhere('tax_id', 'like', "%{$searchQuery}%")
                        ->orWhere('email', 'like', "%{$searchQuery}%");
                });
            })
            ->withCount('houses')
            ->orderBy('name')
            ->paginate(15);

        return response()->json($farms);
    }

    public function getAll()
    {
        $farms = Farm::where('is_active', true)->orderBy('name')->get();
        return response()->json($farms);
    }

    public function show(Farm $farm)
    {
        return response()->json($farm->load('houses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'tax_id' => 'required|string|max:18|unique:farms',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'is_active' => 'boolean'
        ]);

        $farm = Farm::create($validated);
        return response()->json($farm, 201);
    }

    public function update(Request $request, Farm $farm)
    {
        $validated = $request->validate([
            'name' => 'string|max:100',
            'tax_id' => 'string|max:18|unique:farms,tax_id,' . $farm->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'is_active' => 'boolean'
        ]);

        $farm->update($validated);
        return response()->json($farm);
    }

    public function destroy(Farm $farm)
    {
        $farm->delete();
        return response()->json(['message' => 'Farm deleted successfully']);
    }

    public function toggleStatus(Farm $farm)
    {
        $farm->update(['is_active' => !$farm->is_active]);
        return response()->json($farm);
    }

    public function export()
    {
        $farms = Farm::all();
        return response()->json($farms);
    }
}
