<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\Egg;
use App\Models\EggModule\EggClassification;
use Illuminate\Http\Request;

class EggController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $query = Egg::with('flock', 'category', 'classification')
            ->when($searchQuery, function ($q, $searchQuery) {
                $q->where(function ($sub) use ($searchQuery) {
                    $sub->where('traceability_code', 'like', "%{$searchQuery}%")
                        ->orWhereHas('flock', function ($flockQuery) use ($searchQuery) {
                            $flockQuery->where('code', 'like', "%{$searchQuery}%");
                        });
                });
            });

        if ($request->filled('flock_id')) {
            $query->where('flock_id', $request->flock_id);
        }

        if ($request->filled('quality')) {
            $query->where('quality', $request->quality);
        }

        if ($request->filled('destination')) {
            $query->where('destination', $request->destination);
        }

        $eggs = $query->orderBy('lay_date', 'desc')->paginate(15);

        return response()->json($eggs);
    }

    public function getAll()
    {
        $eggs = Egg::with('category', 'flock')
            ->orderBy('lay_date', 'desc')
            ->get();

        return response()->json($eggs);
    }

    public function getByTraceabilityCode($code)
    {
        $egg = Egg::where('traceability_code', $code)
            ->with('flock', 'flock.house', 'category')
            ->firstOrFail();
        
        return response()->json($egg);
    }

    public function show(Egg $egg)
    {
        return response()->json($egg->load('flock', 'category', 'classification', 'inventory'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'flock_id' => 'required|exists:flocks,id',
            'lay_date' => 'required|date',
            'quality' => 'required|in:clean,dirty,cracked,deformed',
            'reject_reason' => 'nullable|string|max:100',
            'destination' => 'in:packaged,reject,broken'
        ]);

        $validated['traceability_code'] = $this->generateTraceabilityCode();
        $egg = Egg::create($validated);
        
        return response()->json($egg, 201);
    }

    public function bulkClassify(Request $request)
    {
        $request->validate([
            'classification_id' => 'required|exists:egg_classifications,id',
            'eggs' => 'required|array',
            'eggs.*.egg_id' => 'required|exists:eggs,id',
            'eggs.*.category_id' => 'nullable|exists:egg_categories,id'
        ]);

        $classification = EggClassification::find($request->classification_id);
        $updated = [];
        
        foreach ($request->eggs as $eggData) {
            $egg = Egg::find($eggData['egg_id']);
            $egg->update([
                'classification_id' => $classification->id,
                'classification_date' => $classification->processing_date,
                'category_id' => $eggData['category_id'] ?? null
            ]);
            $updated[] = $egg;
        }
        
        return response()->json($updated);
    }

    public function update(Request $request, Egg $egg)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:egg_categories,id',
            'quality' => 'in:clean,dirty,cracked,deformed',
            'reject_reason' => 'nullable|string|max:100',
            'destination' => 'in:packaged,reject,broken'
        ]);

        $egg->update($validated);
        return response()->json($egg);
    }

    public function destroy(Egg $egg)
    {
        $egg->delete();
        return response()->json(['message' => 'Egg record deleted successfully']);
    }

    private function generateTraceabilityCode()
    {
        return 'EGG-' . strtoupper(uniqid()) . '-' . date('YmdHis');
    }
}
