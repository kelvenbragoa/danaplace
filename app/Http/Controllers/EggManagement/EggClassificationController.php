<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\EggClassification;
use App\Models\EggModule\Flock;
use Illuminate\Http\Request;

class EggClassificationController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $query = EggClassification::with('flock.house.farm', 'responsible')
            ->when($searchQuery, function ($q, $searchQuery) {
                $q->whereHas('flock', function ($flockQuery) use ($searchQuery) {
                    $flockQuery->where('code', 'like', "%{$searchQuery}%");
                });
            });

        if ($request->filled('flock_id')) {
            $query->where('flock_id', $request->flock_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('processing_date', $request->date);
        }

        $classifications = $query->orderBy('processing_date', 'desc')->paginate(15);

        return response()->json($classifications);
    }

    public function getAll()
    {
        $classifications = EggClassification::with('flock')
            ->orderBy('processing_date', 'desc')
            ->get();

        return response()->json($classifications);
    }

    public function getByFlock(Flock $flock)
    {
        $classifications = EggClassification::where('flock_id', $flock->id)
            ->orderBy('processing_date', 'desc')
            ->paginate(15);
        
        return response()->json($classifications);
    }

    public function show(EggClassification $eggClassification)
    {
        return response()->json($eggClassification->load('flock.house.farm', 'flock.lineage', 'responsible', 'packaging'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'flock_id' => 'required|exists:flocks,id',
            'processing_date' => 'required|date',
            'washed_eggs' => 'integer|min:0',
            'unwashed_eggs' => 'integer|min:0',
            'total_rejects' => 'integer|min:0'
        ]);

        $totalProcessed = $validated['washed_eggs'] + $validated['unwashed_eggs'];
        $validated['reject_percentage'] = $totalProcessed > 0 
            ? ($validated['total_rejects'] / $totalProcessed) * 100 
            : 0;
        $validated['responsible_id'] = auth()->id();

        $classification = EggClassification::create($validated);
        return response()->json($classification->load('flock'), 201);
    }

    public function process(Request $request)
    {
        // Custom processing logic for egg classification
        return $this->store($request);
    }

    public function update(Request $request, EggClassification $eggClassification)
    {
        $validated = $request->validate([
            'flock_id' => 'exists:flocks,id',
            'processing_date' => 'date',
            'washed_eggs' => 'integer|min:0',
            'unwashed_eggs' => 'integer|min:0',
            'total_rejects' => 'integer|min:0',
        ]);

        if (isset($validated['washed_eggs']) || isset($validated['unwashed_eggs']) || isset($validated['total_rejects'])) {
            $washed = $validated['washed_eggs'] ?? $eggClassification->washed_eggs;
            $unwashed = $validated['unwashed_eggs'] ?? $eggClassification->unwashed_eggs;
            $rejects = $validated['total_rejects'] ?? $eggClassification->total_rejects;
            $totalProcessed = $washed + $unwashed;
            $validated['reject_percentage'] = $totalProcessed > 0 ? ($rejects / $totalProcessed) * 100 : 0;
        }

        $eggClassification->update($validated);

        return response()->json($eggClassification->load('flock.house', 'responsible'));
    }

    public function destroy(EggClassification $eggClassification)
    {
        $eggClassification->delete();
        return response()->json(['message' => 'Classification record deleted successfully']);
    }

    public function rejectReport(Request $request)
    {
        $query = EggClassification::query();
        
        if ($request->has('start_date')) {
            $query->where('processing_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->where('processing_date', '<=', $request->end_date);
        }
        
        $report = $query->selectRaw('
            DATE(processing_date) as date,
            SUM(total_rejects) as total_rejects,
            SUM(washed_eggs + unwashed_eggs) as total_processed,
            AVG(reject_percentage) as avg_reject_percentage
        ')->groupBy('date')->orderBy('date', 'desc')->get();
        
        return response()->json($report);
    }
}
