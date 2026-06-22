<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\Flock;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FlockController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $query = Flock::with('house.farm', 'lineage')
            ->when($searchQuery, function ($q, $searchQuery) {
                $q->where('code', 'like', "%{$searchQuery}%");
            });

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('house_id')) {
            $query->where('house_id', $request->house_id);
        }

        $flocks = $query->orderBy('created_at', 'desc')->paginate(15);
        return response()->json($flocks);
    }

    public function getAll()
    {
        $flocks = Flock::with('house', 'lineage')->where('status', 'laying')->get();
        return response()->json($flocks);
    }

    public function getActive()
    {
        $flocks = Flock::with('house.farm', 'lineage')
            ->whereIn('status', ['growing', 'laying'])
            ->orderBy('code')
            ->get();

        return response()->json($flocks);
    }

    public function show(Flock $flock)
    {
        return response()->json($flock->load('house.farm', 'lineage', 'dailyProduction', 'mortality', 'vaccinationSchedule.vaccine', 'eggClassifications'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'house_id' => 'required|exists:houses,id',
            'lineage_id' => 'required|exists:lineages,id',
            'code' => 'required|string|max:50|unique:flocks',
            'birth_date' => 'required|date',
            'housing_date' => 'required|date',
            'initial_bird_count' => 'required|integer|min:1',
            'current_bird_count' => 'required|integer|min:0',
            'expected_disposal_date' => 'nullable|date',
            'status' => 'in:growing,laying,disposed',
            'observations' => 'nullable|string',
            'daily_feed_consumption_kg' => 'nullable|numeric|min:0',
            'daily_water_consumption_liters' => 'nullable|numeric|min:0',
            'daily_light_hours' => 'nullable|numeric|min:0'
        ]);

        $flock = Flock::create($validated);
        return response()->json($flock->load('house', 'lineage'), 201);
    }

    public function update(Request $request, Flock $flock)
    {
        $validated = $request->validate([
            'house_id' => 'exists:houses,id',
            'lineage_id' => 'exists:lineages,id',
            'code' => 'string|max:50|unique:flocks,code,' . $flock->id,
            'birth_date' => 'date',
            'housing_date' => 'date',
            'initial_bird_count' => 'integer|min:1',
            'current_bird_count' => 'integer|min:0',
            'expected_disposal_date' => 'nullable|date',
            'actual_disposal_date' => 'nullable|date',
            'status' => 'in:growing,laying,disposed',
            'observations' => 'nullable|string',
            'daily_feed_consumption_kg' => 'nullable|numeric|min:0',
            'daily_water_consumption_liters' => 'nullable|numeric|min:0',
            'daily_light_hours' => 'nullable|numeric|min:0'
        ]);

        $flock->update($validated);
        return response()->json($flock->load('house', 'lineage'));
    }

    public function destroy(Flock $flock)
    {
        $flock->delete();
        return response()->json(['message' => 'Flock deleted successfully']);
    }

    public function changeStatus(Flock $flock, Request $request)
    {
        $request->validate(['status' => 'required|in:growing,laying,disposed']);
        $flock->update(['status' => $request->status]);
        return response()->json($flock);
    }

    public function dispose(Flock $flock, Request $request)
    {
        $request->validate(['actual_disposal_date' => 'required|date']);
        $flock->update([
            'status' => 'disposed',
            'actual_disposal_date' => $request->actual_disposal_date
        ]);
        return response()->json($flock);
    }

    public function productionChart(Flock $flock)
    {
        $data = $flock->dailyProduction()
            ->where('date', '>=', Carbon::now()->subDays(30))
            ->orderBy('date')
            ->get(['date', 'total_eggs', 'cracked_eggs', 'feed_consumption_kg']);
        
        return response()->json($data);
    }
}
