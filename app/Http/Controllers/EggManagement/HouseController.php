<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\Farm;
use App\Models\EggModule\House;
use Illuminate\Http\Request;

class HouseController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $query = House::with('farm')
            ->withCount('flocks')
            ->when($searchQuery, function ($q, $searchQuery) {
                $q->where(function ($sub) use ($searchQuery) {
                    $sub->where('name', 'like', "%{$searchQuery}%")
                        ->orWhere('code', 'like', "%{$searchQuery}%");
                });
            });

        if ($request->has('farm_id')) {
            $query->where('farm_id', $request->farm_id);
        }

        $houses = $query->orderBy('name')->paginate(15);
        return response()->json($houses);
    }

    public function getAll()
    {
        $houses = House::where('is_active', true)->with('farm')->orderBy('name')->get();
        return response()->json($houses);
    }

    public function getByFarm(Farm $farm)
    {
        $houses = House::where('farm_id', $farm->id)->where('is_active', true)->get();
        return response()->json($houses);
    }

    public function show(House $house)
    {
        return response()->json($house->load('farm', 'flocks.lineage'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'name' => 'required|string|max:50',
            'bird_capacity' => 'required|integer|min:0',
            'boxes' => 'integer|min:0',
            'has_automation' => 'boolean',
            'code' => 'required|string|max:20|unique:houses',
            'is_active' => 'boolean'
        ]);

        $house = House::create($validated);
        return response()->json($house->load('farm'), 201);
    }

    public function update(Request $request, House $house)
    {
        $validated = $request->validate([
            'farm_id' => 'exists:farms,id',
            'name' => 'string|max:50',
            'bird_capacity' => 'integer|min:0',
            'boxes' => 'integer|min:0',
            'has_automation' => 'boolean',
            'code' => 'string|max:20|unique:houses,code,' . $house->id,
            'is_active' => 'boolean'
        ]);

        $house->update($validated);
        return response()->json($house->load('farm'));
    }

    public function destroy(House $house)
    {
        $house->delete();
        return response()->json(['message' => 'House deleted successfully']);
    }

    public function toggleStatus(House $house)
    {
        $house->update(['is_active' => !$house->is_active]);
        return response()->json($house);
    }
}
