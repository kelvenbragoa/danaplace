<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\WorkSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Shift::with(['technicians', 'workSchedule'])
            ->select([
                'shifts.*',
                'work_schedules.name as schedule_name',
                'work_schedules.month as schedule_month',
                'work_schedules.year as schedule_year'
            ])
            ->leftJoin('work_schedules', 'shifts.work_schedule_id', '=', 'work_schedules.id');

        // Filtros
        if ($request->filled('schedule_id')) {
            $query->bySchedule($request->schedule_id);
        }

        if ($request->filled('shift_type')) {
            $query->byShiftType($request->shift_type);
        }

        if ($request->filled('date')) {
            $query->byDate($request->date);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Ordenação
        $query->orderBy('date', 'desc')
              ->orderBy('start_time', 'asc');

        // Estatísticas
        $stats = Shift::getStats($request->only(['schedule_id', 'shift_type', 'date']));

        // Paginação
        $shifts = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $shifts->items(),
            'current_page' => $shifts->currentPage(),
            'last_page' => $shifts->lastPage(),
            'per_page' => $shifts->perPage(),
            'total' => $shifts->total(),
            'stats' => $stats
        ]);
    }

    /**
     * Initial data for forms
     */
    public function initialData()
    {
        $schedules = WorkSchedule::select(['id', 'name', 'year', 'month'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $technicians = User::select(['id', 'name', 'email'])
            ->whereHas('roles', function ($query) {
                $query->where('name', 'technician'); // Assumindo que existe role de técnico
            })
            ->orWhereNull('id') // Fallback: todos os usuários se não houver sistema de roles
            ->orderBy('name')
            ->get();

        return response()->json([
            'schedules' => $schedules,
            'technicians' => $technicians
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schedules = WorkSchedule::select(['id', 'name', 'year', 'month'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $technicians = User::select(['id', 'name', 'email'])
            ->orderBy('name')
            ->get();

        // Turnos recentes para cópia
        $recentShifts = Shift::with(['technicians', 'workSchedule'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'schedules' => $schedules,
            'technicians' => $technicians,
            'shifts' => $recentShifts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'work_schedule_id' => 'required|exists:work_schedules,id',
            'date' => 'required|date',
            'name' => 'required|string|max:255',
            'shift_type' => 'required|in:morning,afternoon,evening,night',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'description' => 'nullable|string',
            'technician_ids' => 'array',
            'technician_ids.*' => 'exists:users,id'
        ]);

        // Validar se a data está dentro do período da escala
        $schedule = WorkSchedule::findOrFail($request->work_schedule_id);
        $shiftDate = Carbon::parse($request->date);
        
        if (!$shiftDate->between($schedule->start_date, $schedule->end_date)) {
            return response()->json([
                'message' => 'A data do turno deve estar dentro do período da escala.',
                'errors' => ['date' => ['Data fora do período da escala.']]
            ], 422);
        }

        DB::beginTransaction();

        try {
            $shift = Shift::create($request->only([
                'work_schedule_id', 'date', 'name', 'shift_type',
                'start_time', 'end_time', 'description'
            ]));

            // Atribuir técnicos
            if ($request->filled('technician_ids')) {
                $shift->technicians()->attach($request->technician_ids);
            }

            DB::commit();

            $shift->load(['technicians', 'workSchedule']);

            return response()->json($shift, 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Erro ao criar turno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Shift $shift)
    {
        $shift->load(['technicians', 'workSchedule']);
        
        // Verificar conflitos
        $conflicts = $shift->getConflictingShifts();
        
        return response()->json([
            'shift' => $shift,
            'conflicts' => $conflicts,
            'calculated_status' => $shift->calculated_status,
            'status_label' => $shift->status_label,
            'duration' => $shift->duration,
            'can_edit' => $shift->can_edit,
            'can_delete' => $shift->can_delete,
            'can_toggle_status' => $shift->can_toggle_status
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shift $shift)
    {
        $shift->load(['technicians', 'workSchedule']);

        $schedules = WorkSchedule::select(['id', 'name', 'year', 'month'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $technicians = User::select(['id', 'name', 'email'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'shift' => $shift,
            'schedules' => $schedules,
            'technicians' => $technicians
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'work_schedule_id' => 'required|exists:work_schedules,id',
            'date' => 'required|date',
            'name' => 'required|string|max:255',
            'shift_type' => 'required|in:morning,afternoon,evening,night',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'description' => 'nullable|string',
            'technician_ids' => 'array',
            'technician_ids.*' => 'exists:users,id'
        ]);

        // Verificar se pode ser editado
        if (!$shift->can_edit) {
            return response()->json(['message' => 'Este turno não pode ser editado.'], 422);
        }

        // Validar se a data está dentro do período da escala
        $schedule = WorkSchedule::findOrFail($request->work_schedule_id);
        $shiftDate = Carbon::parse($request->date);
        
        if (!$shiftDate->between($schedule->start_date, $schedule->end_date)) {
            return response()->json([
                'message' => 'A data do turno deve estar dentro do período da escala.',
                'errors' => ['date' => ['Data fora do período da escala.']]
            ], 422);
        }

        DB::beginTransaction();

        try {
            $shift->update($request->only([
                'work_schedule_id', 'date', 'name', 'shift_type',
                'start_time', 'end_time', 'description'
            ]));

            // Atualizar técnicos
            if ($request->has('technician_ids')) {
                $shift->technicians()->sync($request->technician_ids ?? []);
            }

            DB::commit();

            $shift->load(['technicians', 'workSchedule']);

            return response()->json($shift);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Erro ao atualizar turno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shift $shift)
    {
        if (!$shift->can_delete) {
            return response()->json(['message' => 'Este turno não pode ser excluído.'], 422);
        }

        try {
            $shift->delete();
            return response()->json(['message' => 'Turno excluído com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao excluir turno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Copy shift to multiple dates
     */
    public function copy(Request $request, Shift $shift)
    {
        $request->validate([
            'dates' => 'required|array',
            'dates.*' => 'date',
            'copy_technicians' => 'boolean'
        ]);

        DB::beginTransaction();

        try {
            $copiedShifts = [];

            foreach ($request->dates as $date) {
                // Verificar se já existe turno na data
                $existingShift = Shift::where('work_schedule_id', $shift->work_schedule_id)
                    ->where('date', $date)
                    ->where('start_time', $shift->start_time)
                    ->first();

                if (!$existingShift) {
                    $copiedShift = $shift->copyToDate($date, $request->boolean('copy_technicians'));
                    $copiedShifts[] = $copiedShift;
                }
            }

            DB::commit();

            return response()->json([
                'message' => count($copiedShifts) . ' turnos copiados com sucesso.',
                'copied_shifts' => $copiedShifts
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Erro ao copiar turnos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Toggle shift status
     */
    public function toggleStatus(Shift $shift)
    {
        if (!$shift->can_toggle_status) {
            return response()->json(['message' => 'O status deste turno não pode ser alterado.'], 422);
        }

        try {
            $shift->toggleStatus();
            return response()->json([
                'status' => $shift->calculated_status,
                'status_label' => $shift->status_label
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao alterar status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get shifts by schedule for copying
     */
    public function bySchedule(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:work_schedules,id'
        ]);

        $shifts = Shift::with(['technicians'])
            ->bySchedule($request->schedule_id)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return response()->json(['data' => $shifts]);
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,copy,assign_technicians',
            'shift_ids' => 'required|array',
            'shift_ids.*' => 'exists:shifts,id',
            'technician_ids' => 'sometimes|array',
            'technician_ids.*' => 'exists:users,id',
            'target_dates' => 'sometimes|array',
            'target_dates.*' => 'date'
        ]);

        $shifts = Shift::whereIn('id', $request->shift_ids)->get();
        $results = [];

        DB::beginTransaction();

        try {
            switch ($request->action) {
                case 'delete':
                    foreach ($shifts as $shift) {
                        if ($shift->can_delete) {
                            $shift->delete();
                            $results[] = "Turno {$shift->name} excluído.";
                        }
                    }
                    break;

                case 'assign_technicians':
                    foreach ($shifts as $shift) {
                        if ($shift->can_edit) {
                            $shift->technicians()->sync($request->technician_ids ?? []);
                            $results[] = "Técnicos atribuídos ao turno {$shift->name}.";
                        }
                    }
                    break;

                case 'copy':
                    foreach ($shifts as $shift) {
                        foreach ($request->target_dates as $date) {
                            $copiedShift = $shift->copyToDate($date, true);
                            $results[] = "Turno {$shift->name} copiado para {$date}.";
                        }
                    }
                    break;
            }

            DB::commit();

            return response()->json([
                'message' => 'Operação realizada com sucesso.',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Erro na operação: ' . $e->getMessage()], 500);
        }
    }
}
