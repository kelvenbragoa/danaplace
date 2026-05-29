<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TechnicianAbsence;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TechnicianAbsenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = TechnicianAbsence::with(['technician.department', 'createdByUser', 'approvedByUser']);

        // Filtros
        if (request('technician_id')) {
            $query->where('technician_id', request('technician_id'));
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('type')) {
            $query->where('type', request('type'));
        }

        if (request('date_from')) {
            $query->whereDate('date', '>=', request('date_from'));
        }

        if (request('date_to')) {
            $query->whereDate('date', '<=', request('date_to'));
        }

        if (request('month') && request('year')) {
            $query->forMonth(request('month'), request('year'));
        }

        if (request('query')) {
            $searchQuery = request('query');
            $query->whereHas('technician', function($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%");
            })->orWhere('reason', 'like', "%{$searchQuery}%");
        }

        $absences = $query->orderBy('date', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->paginate(15);

        return response()->json($absences);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $technicians = Technician::active()
            ->with('department')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'technicians' => $technicians
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'technician_id' => 'required|exists:technicians,id',
            'date' => 'required|date|before_or_equal:today',
            'type' => 'required|in:absence,late_arrival,early_departure',
            'hours_lost' => 'required|numeric|min:0|max:24',
            'reason' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar se já existe uma falta para o mesmo técnico na mesma data
        $existingAbsence = TechnicianAbsence::where('technician_id', $request->technician_id)
                                          ->where('date', $request->date)
                                          ->where('type', $request->type)
                                          ->first();

        if ($existingAbsence) {
            return response()->json([
                'error' => 'Já existe um registro de ' . $existingAbsence->type_label . ' para este técnico na data informada.'
            ], 422);
        }

        $absence = TechnicianAbsence::create([
            'technician_id' => $request->technician_id,
            'date' => $request->date,
            'type' => $request->type,
            'hours_lost' => $request->hours_lost,
            'reason' => $request->reason,
            'created_by_user_id' => Auth::id()
        ]);

        $absence->load(['technician.department', 'createdByUser']);

        return response()->json([
            'message' => 'Falta registrada com sucesso!',
            'absence' => $absence
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $absence = TechnicianAbsence::with([
            'technician.department',
            'technician.area',
            'createdByUser',
            'approvedByUser'
        ])->findOrFail($id);

        return response()->json([
            'absence' => $absence
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $absence = TechnicianAbsence::with(['technician.department'])->findOrFail($id);

        if (!$absence->canBeEdited()) {
            return response()->json(['error' => 'Esta falta não pode ser editada pois já foi aprovada ou rejeitada.'], 422);
        }

        $technicians = Technician::active()
            ->with('department')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'absence' => $absence,
            'technicians' => $technicians
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $absence = TechnicianAbsence::findOrFail($id);

        if (!$absence->canBeEdited()) {
            return response()->json(['error' => 'Esta falta não pode ser editada pois já foi aprovada ou rejeitada.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'technician_id' => 'required|exists:technicians,id',
            'date' => 'required|date|before_or_equal:today',
            'type' => 'required|in:absence,late_arrival,early_departure',
            'hours_lost' => 'required|numeric|min:0|max:24',
            'reason' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar duplicatas (excluindo o registro atual)
        $existingAbsence = TechnicianAbsence::where('technician_id', $request->technician_id)
                                          ->where('date', $request->date)
                                          ->where('type', $request->type)
                                          ->where('id', '!=', $id)
                                          ->first();

        if ($existingAbsence) {
            return response()->json([
                'error' => 'Já existe um registro de ' . $existingAbsence->type_label . ' para este técnico na data informada.'
            ], 422);
        }

        $absence->update([
            'technician_id' => $request->technician_id,
            'date' => $request->date,
            'type' => $request->type,
            'hours_lost' => $request->hours_lost,
            'reason' => $request->reason
        ]);

        $absence->load(['technician.department', 'createdByUser', 'approvedByUser']);

        return response()->json([
            'message' => 'Falta atualizada com sucesso!',
            'absence' => $absence
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $absence = TechnicianAbsence::findOrFail($id);

        if (!$absence->canBeDeleted()) {
            return response()->json(['error' => 'Esta falta não pode ser excluída pois já foi aprovada ou rejeitada.'], 422);
        }

        $absence->delete();

        return response()->json(['message' => 'Falta excluída com sucesso!']);
    }

    /**
     * Aprovar falta
     */
    public function approve(Request $request, string $id)
    {
        $absence = TechnicianAbsence::findOrFail($id);

        if ($absence->status !== 'pending') {
            return response()->json(['error' => 'Só é possível aprovar faltas pendentes.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'observations' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $absence->approve(Auth::id(), $request->observations);
        $absence->load(['technician.department', 'createdByUser', 'approvedByUser']);

        return response()->json([
            'message' => 'Falta aprovada com sucesso!',
            'absence' => $absence
        ]);
    }

    /**
     * Rejeitar falta
     */
    public function reject(Request $request, string $id)
    {
        $absence = TechnicianAbsence::findOrFail($id);

        if ($absence->status !== 'pending') {
            return response()->json(['error' => 'Só é possível rejeitar faltas pendentes.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'observations' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $absence->reject(Auth::id(), $request->observations);
        $absence->load(['technician.department', 'createdByUser', 'approvedByUser']);

        return response()->json([
            'message' => 'Falta rejeitada com sucesso!',
            'absence' => $absence
        ]);
    }

    /**
     * Obter relatório de faltas
     */
    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2050',
            'technician_id' => 'nullable|exists:technicians,id',
            'status' => 'nullable|in:pending,approved,rejected'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = TechnicianAbsence::with(['technician.department', 'createdByUser', 'approvedByUser'])
                                 ->forMonth($request->month, $request->year);

        if ($request->technician_id) {
            $query->forTechnician($request->technician_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $absences = $query->orderBy('date', 'desc')->get();

        // Calcular estatísticas
        $statistics = [
            'total_absences' => $absences->count(),
            'pending_count' => $absences->where('status', 'pending')->count(),
            'approved_count' => $absences->where('status', 'approved')->count(),
            'rejected_count' => $absences->where('status', 'rejected')->count(),
            'total_hours_lost' => $absences->where('status', 'approved')->sum('hours_lost'),
            'by_type' => [
                'absence' => $absences->where('type', 'absence')->count(),
                'late_arrival' => $absences->where('type', 'late_arrival')->count(),
                'early_departure' => $absences->where('type', 'early_departure')->count()
            ],
            'by_technician' => $absences->groupBy('technician_id')->map(function($group) {
                $technician = $group->first()->technician;
                return [
                    'technician_name' => $technician->name,
                    'department' => $technician->department->name ?? '-',
                    'total_absences' => $group->count(),
                    'total_hours_lost' => $group->where('status', 'approved')->sum('hours_lost')
                ];
            })->values()
        ];

        return response()->json([
            'absences' => $absences,
            'statistics' => $statistics,
            'period' => [
                'month' => $request->month,
                'year' => $request->year,
                'month_name' => Carbon::createFromDate($request->year, $request->month, 1)->format('F')
            ]
        ]);
    }

    /**
     * Obter técnicos para filtros
     */
    public function getTechnicians()
    {
        $technicians = Technician::active()
            ->with('department')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'technicians' => $technicians
        ]);
    }
}
