<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\Flock;
use App\Models\EggModule\Mortality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MortalityController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $query = Mortality::with('flock.house.farm', 'responsible')
            ->when($searchQuery, function ($q, $searchQuery) {
                $q->where(function ($sub) use ($searchQuery) {
                    $sub->where('probable_cause', 'like', "%{$searchQuery}%")
                        ->orWhereHas('flock', function ($flockQuery) use ($searchQuery) {
                            $flockQuery->where('code', 'like', "%{$searchQuery}%");
                        });
                });
            });

        if ($request->filled('flock_id')) {
            $query->where('flock_id', $request->flock_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $mortalities = $query->orderBy('date', 'desc')->paginate(15);

        return response()->json($mortalities);
    }

    public function getByFlock(Flock $flock, Request $request)
    {
        $query = Mortality::with('responsible')
            ->where('flock_id', $flock->id);

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $mortalities = $query->orderBy('date', 'desc')->paginate(15);

        return response()->json($mortalities);
    }

    public function dashboardStats()
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();

        return response()->json([
            'deaths_today' => Mortality::whereDate('date', $today)->sum('quantity'),
            'deaths_month' => Mortality::whereDate('date', '>=', $startOfMonth)->sum('quantity'),
            'records_today' => Mortality::whereDate('date', $today)->count(),
            'records_month' => Mortality::whereDate('date', '>=', $startOfMonth)->count(),
            'necropsies_month' => Mortality::whereDate('date', '>=', $startOfMonth)
                ->where('necropsy_performed', true)
                ->count(),
        ]);
    }

    public function show(Mortality $mortality)
    {
        return response()->json($mortality->load('flock.house.farm', 'flock.lineage', 'responsible'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'flock_id' => 'required|exists:flocks,id',
            'date' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'probable_cause' => 'nullable|string|max:255',
            'necropsy_performed' => 'boolean',
            'necropsy_report' => 'nullable|string',
        ]);

        $validated['necropsy_performed'] = (bool) ($validated['necropsy_performed'] ?? false);
        $validated['responsible_id'] = auth()->id();

        $mortality = DB::transaction(function () use ($validated) {
            $flock = Flock::lockForUpdate()->findOrFail($validated['flock_id']);

            if ($validated['quantity'] > $flock->current_bird_count) {
                throw ValidationException::withMessages([
                    'quantity' => 'A quantidade não pode ser superior às aves atuais do lote.',
                ]);
            }

            $mortality = Mortality::create($validated);
            $flock->decrement('current_bird_count', $validated['quantity']);

            return $mortality;
        });

        return response()->json($mortality->load('flock.house', 'responsible'), 201);
    }

    public function update(Request $request, Mortality $mortality)
    {
        $validated = $request->validate([
            'flock_id' => 'exists:flocks,id',
            'date' => 'date',
            'quantity' => 'integer|min:1',
            'probable_cause' => 'nullable|string|max:255',
            'necropsy_performed' => 'boolean',
            'necropsy_report' => 'nullable|string',
        ]);

        $mortality = DB::transaction(function () use ($validated, $mortality) {
            $newQuantity = $validated['quantity'] ?? $mortality->quantity;
            $newFlockId = $validated['flock_id'] ?? $mortality->flock_id;

            if ($newFlockId != $mortality->flock_id) {
                $oldFlock = Flock::lockForUpdate()->findOrFail($mortality->flock_id);
                $oldFlock->increment('current_bird_count', $mortality->quantity);

                $newFlock = Flock::lockForUpdate()->findOrFail($newFlockId);
                if ($newQuantity > $newFlock->current_bird_count) {
                    throw ValidationException::withMessages([
                        'quantity' => 'A quantidade não pode ser superior às aves atuais do lote.',
                    ]);
                }
                $newFlock->decrement('current_bird_count', $newQuantity);
            } else {
                $flock = Flock::lockForUpdate()->findOrFail($mortality->flock_id);
                $quantityDiff = $newQuantity - $mortality->quantity;

                if ($quantityDiff > 0 && $quantityDiff > $flock->current_bird_count) {
                    throw ValidationException::withMessages([
                        'quantity' => 'A quantidade não pode ser superior às aves atuais do lote.',
                    ]);
                }

                if ($quantityDiff > 0) {
                    $flock->decrement('current_bird_count', $quantityDiff);
                } elseif ($quantityDiff < 0) {
                    $flock->increment('current_bird_count', abs($quantityDiff));
                }
            }

            if (isset($validated['necropsy_performed'])) {
                $validated['necropsy_performed'] = (bool) $validated['necropsy_performed'];
            }

            $mortality->update($validated);

            return $mortality;
        });

        return response()->json($mortality->load('flock.house', 'responsible'));
    }

    public function destroy(Mortality $mortality)
    {
        DB::transaction(function () use ($mortality) {
            $flock = Flock::lockForUpdate()->findOrFail($mortality->flock_id);
            $flock->increment('current_bird_count', $mortality->quantity);
            $mortality->delete();
        });

        return response()->json(['message' => 'Mortality record deleted successfully']);
    }
}
