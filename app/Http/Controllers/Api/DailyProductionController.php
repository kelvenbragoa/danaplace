<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EggModule\DailyProduction;
use App\Models\EggModule\Flock;
use Illuminate\Http\Request;

class DailyProductionController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $query = DailyProduction::with('flock.house.farm', 'responsible')
            ->when($searchQuery, function ($q, $searchQuery) {
                $q->whereHas('flock', function ($flockQuery) use ($searchQuery) {
                    $flockQuery->where('code', 'like', "%{$searchQuery}%");
                });
            });

        if ($request->filled('flock_id')) {
            $query->where('flock_id', $request->flock_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $productions = $query->orderBy('date', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $productions,
        ]);
    }

    public function getByFlock(Flock $flock)
    {
        $productions = DailyProduction::where('flock_id', $flock->id)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $productions,
        ]);
    }

    public function getByDate(string $date)
    {
        $productions = DailyProduction::with('flock.house')
            ->whereDate('date', $date)
            ->orderBy('flock_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $productions,
        ]);
    }

    public function show(DailyProduction $dailyProduction)
    {
        return response()->json([
            'success' => true,
            'data' => $dailyProduction->load('flock.house.farm', 'flock.lineage', 'responsible'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduction($request);

        $production = DailyProduction::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Produção registada com sucesso',
            'data' => $production->load('flock.house', 'responsible'),
        ], 201);
    }

    public function update(Request $request, DailyProduction $dailyProduction)
    {
        $validated = $this->validateProduction($request, $dailyProduction->id);

        $dailyProduction->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Produção actualizada com sucesso',
            'data' => $dailyProduction->load('flock.house', 'responsible'),
        ]);
    }

    public function destroy(DailyProduction $dailyProduction)
    {
        $dailyProduction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produção eliminada com sucesso',
        ]);
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'productions' => 'required|array|min:1',
            'productions.*.flock_id' => 'required|exists:flocks,id',
            'productions.*.total_eggs' => 'required|integer|min:0',
            'productions.*.cracked_eggs' => 'integer|min:0',
            'productions.*.dirty_eggs' => 'integer|min:0',
            'productions.*.deformed_eggs' => 'integer|min:0',
            'productions.*.feed_consumption_kg' => 'numeric|min:0',
            'productions.*.water_consumption_liters' => 'numeric|min:0',
            'productions.*.light_hours' => 'numeric|min:0',
            'productions.*.observations' => 'nullable|string',
        ]);

        $created = [];

        foreach ($request->productions as $item) {
            $item['date'] = $request->date;
            $item['responsible_id'] = auth()->id();
            $item = $this->prepareProductionData($item);

            $production = DailyProduction::updateOrCreate(
                [
                    'flock_id' => $item['flock_id'],
                    'date' => $item['date'],
                ],
                $item
            );

            $created[] = $production->load('flock.house');
        }

        return response()->json([
            'success' => true,
            'message' => 'Produções registadas com sucesso',
            'data' => $created,
        ], 201);
    }

    private function validateProduction(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'flock_id' => 'required|exists:flocks,id',
            'date' => 'required|date',
            'total_eggs' => 'required|integer|min:0',
            'cracked_eggs' => 'integer|min:0',
            'dirty_eggs' => 'integer|min:0',
            'deformed_eggs' => 'integer|min:0',
            'clean_eggs' => 'integer|min:0',
            'feed_consumption_kg' => 'numeric|min:0',
            'water_consumption_liters' => 'numeric|min:0',
            'light_hours' => 'numeric|min:0',
            'responsible_id' => 'nullable|exists:users,id',
            'observations' => 'nullable|string',
        ]);

        return $this->prepareProductionData($validated);
    }

    private function prepareProductionData(array $data): array
    {
        $data['cracked_eggs'] = $data['cracked_eggs'] ?? 0;
        $data['dirty_eggs'] = $data['dirty_eggs'] ?? 0;
        $data['deformed_eggs'] = $data['deformed_eggs'] ?? 0;
        $data['feed_consumption_kg'] = $data['feed_consumption_kg'] ?? 0;
        $data['water_consumption_liters'] = $data['water_consumption_liters'] ?? 0;
        $data['light_hours'] = $data['light_hours'] ?? 0;
        $data['responsible_id'] = $data['responsible_id'] ?? auth()->id();
        $data['clean_eggs'] = max(
            0,
            ($data['total_eggs'] ?? 0)
            - $data['cracked_eggs']
            - $data['dirty_eggs']
            - $data['deformed_eggs']
        );

        return $data;
    }
}
