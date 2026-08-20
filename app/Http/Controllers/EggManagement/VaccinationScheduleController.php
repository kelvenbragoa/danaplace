<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\Flock;
use App\Models\EggModule\VaccineSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VaccinationScheduleController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $query = VaccineSchedule::with('flock.house.farm', 'vaccine', 'responsible')
            ->when($searchQuery, function ($q, $searchQuery) {
                $q->where(function ($sub) use ($searchQuery) {
                    $sub->whereHas('flock', function ($flockQuery) use ($searchQuery) {
                        $flockQuery->where('code', 'like', "%{$searchQuery}%");
                    })->orWhereHas('vaccine', function ($vaccineQuery) use ($searchQuery) {
                        $vaccineQuery->where('name', 'like', "%{$searchQuery}%");
                    });
                });
            });

        if ($request->filled('flock_id')) {
            $query->where('flock_id', $request->flock_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        $schedules = $query->orderBy('scheduled_date', 'asc')->paginate(15);

        return response()->json($schedules);
    }

    public function getByFlock(Flock $flock)
    {
        $schedules = VaccineSchedule::where('flock_id', $flock->id)
            ->with('vaccine')
            ->orderBy('scheduled_date', 'asc')
            ->get();
        
        return response()->json($schedules);
    }

    public function pendingToday()
    {
        $schedules = VaccineSchedule::where('status', 'pending')
            ->where('scheduled_date', '<=', Carbon::today())
            ->with('flock', 'vaccine')
            ->get();
        
        return response()->json($schedules);
    }

    public function show(VaccineSchedule $vaccinationSchedule)
    {
        return response()->json($vaccinationSchedule->load('flock.house.farm', 'flock.lineage', 'vaccine', 'responsible'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'flock_id' => 'required|exists:flocks,id',
            'vaccine_id' => 'required|exists:vaccines,id',
            'scheduled_date' => 'required|date',
            'administration_route' => 'required|in:injectable,water,feed',
            'dosage' => 'nullable|string|max:50',
            'observations' => 'nullable|string'
        ]);

        $validated['status'] = 'pending';
        $schedule = VaccineSchedule::create($validated);
        
        return response()->json($schedule->load('flock', 'vaccine'), 201);
    }

    public function apply(Request $request, VaccineSchedule $vaccinationSchedule)
    {
        $request->validate([
            'application_date' => 'required|date',
            'batch_number' => 'nullable|string'
        ]);

        $vaccinationSchedule->update([
            'application_date' => $request->application_date,
            'status' => 'applied',
            'responsible_id' => auth()->id()
        ]);

        return response()->json($vaccinationSchedule->load('flock.house', 'vaccine', 'responsible'));
    }

    public function cancel(VaccineSchedule $vaccinationSchedule)
    {
        $vaccinationSchedule->update(['status' => 'canceled']);
        return response()->json($vaccinationSchedule->load('flock.house', 'vaccine', 'responsible'));
    }

    public function copy(VaccineSchedule $vaccinationSchedule)
    {
        $copy = $vaccinationSchedule->replicate([
            'application_date',
            'responsible_id',
            'status',
        ]);

        $copy->status = 'pending';
        $copy->application_date = null;
        $copy->responsible_id = null;
        $copy->save();

        return response()->json(
            $copy->load('flock.house', 'vaccine', 'responsible'),
            201
        );
    }

    public function update(Request $request, VaccineSchedule $vaccinationSchedule)
    {
        $validated = $request->validate([
            'flock_id' => 'exists:flocks,id',
            'vaccine_id' => 'exists:vaccines,id',
            'scheduled_date' => 'date',
            'administration_route' => 'in:injectable,water,feed',
            'dosage' => 'nullable|string|max:50',
            'observations' => 'nullable|string',
        ]);

        $vaccinationSchedule->update($validated);

        return response()->json($vaccinationSchedule->load('flock.house', 'vaccine', 'responsible'));
    }

    public function destroy(VaccineSchedule $vaccinationSchedule)
    {
        $vaccinationSchedule->delete();
        return response()->json(['message' => 'Schedule deleted successfully']);
    }
}
