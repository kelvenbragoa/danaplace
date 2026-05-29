<?php

namespace App\Http\Controllers;

use App\Models\WorkSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WorkSchedule::query()
            ->with(['shifts.technicians', 'creator'])
            ->withCount('shifts');

        // Filtros
        if ($request->filled('year')) {
            $query->byYear($request->year);
        }

        if ($request->filled('month')) {
            $query->byMonth($request->month);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Ordenação
        $query->orderBy('year', 'desc')
              ->orderBy('month', 'desc')
              ->orderBy('created_at', 'desc');

        // Paginação ou lista completa
        if ($request->boolean('paginate', true)) {
            $schedules = $query->paginate($request->get('per_page', 15));
            
            // Adicionar estatísticas para cada escala
            foreach ($schedules->items() as $schedule) {
                $schedule->stats = $schedule->getStats();
            }
        } else {
            $schedules = $query->get()->map(function ($schedule) {
                $schedule->stats = $schedule->getStats();
                return $schedule;
            });
        }

        return response()->json($schedules);
    }

    /**
     * Dashboard data
     */
    public function dashboard()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Escala ativa atual
        $currentSchedule = WorkSchedule::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->where('status', 'active')
            ->with(['shifts.technicians'])
            ->first();

        // Estatísticas gerais
        $stats = [
            'total_schedules' => WorkSchedule::count(),
            'active_schedules' => WorkSchedule::where('status', 'active')->count(),
            'current_technicians' => $currentSchedule 
                ? $currentSchedule->shifts()->join('shift_technician', 'shifts.id', '=', 'shift_technician.shift_id')
                    ->distinct('shift_technician.user_id')->count()
                : 0,
            'shifts_today' => $currentSchedule 
                ? $currentSchedule->shifts()->whereDate('date', now()->format('Y-m-d'))->count()
                : 0
        ];

        // Turnos atuais (ativos agora)
        $currentShifts = [];
        $upcomingShifts = [];

        if ($currentSchedule) {
            $currentShifts = $currentSchedule->shifts()
                ->with(['technicians'])
                ->active()
                ->get();

            $upcomingShifts = $currentSchedule->shifts()
                ->with(['technicians'])
                ->upcoming()
                ->orderBy('date')
                ->orderBy('start_time')
                ->limit(5)
                ->get();
        }

        // Técnicos ativos hoje
        $activeTechnicians = collect();
        if ($currentSchedule) {
            $todayShifts = $currentSchedule->shifts()
                ->with(['technicians'])
                ->whereDate('date', now()->format('Y-m-d'))
                ->get();
                
            $activeTechnicians = $todayShifts->pluck('technicians')->flatten()->unique('id');
        }

        return response()->json([
            'stats' => $stats,
            'current_schedule' => $currentSchedule,
            'current_shifts' => $currentShifts,
            'upcoming_shifts' => $upcomingShifts,
            'active_technicians' => $activeTechnicians,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obter escalas existentes para cópia
        $existingSchedules = WorkSchedule::with('shifts.technicians')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'existing_schedules' => $existingSchedules,
            'years' => range(now()->year, now()->year + 2),
            'months' => collect(range(1, 12))->map(function ($month) {
                return [
                    'value' => $month,
                    'label' => Carbon::create(null, $month)->locale('pt_BR')->monthName
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:' . (now()->year - 1),
            'month' => 'required|integer|min:1|max:12',
            'description' => 'nullable|string',
            'auto_generate_days' => 'boolean',
            'copy_from_schedule' => 'nullable|exists:work_schedules,id',
            'copy_technicians' => 'boolean'
        ]);

        // Verificar se já existe uma escala para o mês/ano
        $existingSchedule = WorkSchedule::where('year', $request->year)
            ->where('month', $request->month)
            ->first();

        if ($existingSchedule) {
            return response()->json([
                'message' => 'Já existe uma escala para este mês e ano.',
                'errors' => ['month' => ['Já existe uma escala para este período.']]
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Se está copiando de uma escala existente
            if ($request->filled('copy_from_schedule')) {
                $schedule = WorkSchedule::copySchedule(
                    $request->copy_from_schedule,
                    $request->only(['name', 'year', 'month', 'description']),
                    $request->boolean('copy_technicians')
                );
            } else {
                // Criar nova escala
                $schedule = WorkSchedule::create([
                    'name' => $request->name,
                    'year' => $request->year,
                    'month' => $request->month,
                    'description' => $request->description,
                    'auto_generate_days' => $request->boolean('auto_generate_days'),
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            $schedule->load(['shifts.technicians', 'creator']);
            $schedule->stats = $schedule->getStats();

            return response()->json($schedule, 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Erro ao criar escala: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkSchedule $workSchedule)
    {
        $workSchedule->load(['shifts.technicians', 'creator']);
        $workSchedule->stats = $workSchedule->getStats();

        // Organizar turnos por data para o calendário
        $shiftsByDate = $workSchedule->shifts()
            ->with(['technicians'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(function ($shift) {
                return $shift->date->format('Y-m-d');
            });

        return response()->json([
            'schedule' => $workSchedule,
            'shifts_by_date' => $shiftsByDate,
            'calendar_data' => $this->generateCalendarData($workSchedule)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkSchedule $workSchedule)
    {
        $workSchedule->load(['shifts.technicians', 'creator']);

        return response()->json([
            'schedule' => $workSchedule,
            'years' => range(now()->year - 1, now()->year + 2),
            'months' => collect(range(1, 12))->map(function ($month) {
                return [
                    'value' => $month,
                    'label' => Carbon::create(null, $month)->locale('pt_BR')->monthName
                ];
            })
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkSchedule $workSchedule)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:' . (now()->year - 1),
            'month' => 'required|integer|min:1|max:12',
            'description' => 'nullable|string',
            'status' => 'in:draft,active,inactive,archived'
        ]);

        // Verificar se já existe outra escala para o mês/ano
        $existingSchedule = WorkSchedule::where('year', $request->year)
            ->where('month', $request->month)
            ->where('id', '!=', $workSchedule->id)
            ->first();

        if ($existingSchedule) {
            return response()->json([
                'message' => 'Já existe uma escala para este mês e ano.',
                'errors' => ['month' => ['Já existe uma escala para este período.']]
            ], 422);
        }

        $workSchedule->update($request->only([
            'name', 'year', 'month', 'description', 'status'
        ]));

        $workSchedule->load(['shifts.technicians', 'creator']);
        $workSchedule->stats = $workSchedule->getStats();

        return response()->json($workSchedule);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkSchedule $workSchedule)
    {
        try {
            $workSchedule->delete();
            return response()->json(['message' => 'Escala excluída com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao excluir escala: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Copy existing schedule
     */
    public function copy(Request $request)
    {
        $request->validate([
            'source_id' => 'required|exists:work_schedules,id',
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:' . (now()->year - 1),
            'month' => 'required|integer|min:1|max:12',
            'description' => 'nullable|string',
            'copy_technicians' => 'boolean'
        ]);

        // Verificar se já existe uma escala para o mês/ano
        $existingSchedule = WorkSchedule::where('year', $request->year)
            ->where('month', $request->month)
            ->first();

        if ($existingSchedule) {
            return response()->json([
                'message' => 'Já existe uma escala para este mês e ano.',
                'errors' => ['month' => ['Já existe uma escala para este período.']]
            ], 422);
        }

        DB::beginTransaction();

        try {
            $schedule = WorkSchedule::copySchedule(
                $request->source_id,
                $request->only(['name', 'year', 'month', 'description']),
                $request->boolean('copy_technicians')
            );

            DB::commit();

            $schedule->load(['shifts.technicians', 'creator']);
            $schedule->stats = $schedule->getStats();

            return response()->json($schedule, 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Erro ao copiar escala: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Toggle schedule status
     */
    public function toggleStatus(WorkSchedule $workSchedule)
    {
        $newStatus = $workSchedule->status === 'active' ? 'inactive' : 'active';
        
        // Se ativando, desativar outras escalas do mesmo mês
        if ($newStatus === 'active') {
            WorkSchedule::where('year', $workSchedule->year)
                ->where('month', $workSchedule->month)
                ->where('id', '!=', $workSchedule->id)
                ->update(['status' => 'inactive']);
        }
        
        $workSchedule->update(['status' => $newStatus]);
        
        return response()->json(['status' => $newStatus]);
    }

    /**
     * Client view - Public endpoint for viewing current schedules
     */
    public function clientView()
    {
        $currentSchedule = WorkSchedule::where('status', 'active')
            ->with(['shifts.technicians'])
            ->first();

        if (!$currentSchedule) {
            return response()->json([
                'message' => 'Nenhuma escala ativa encontrada',
                'current_shifts' => [],
                'active_technicians' => []
            ]);
        }

        // Turnos ativos agora
        $currentShifts = $currentSchedule->shifts()
            ->with(['technicians'])
            ->active()
            ->get();

        // Próximos turnos (hoje)
        $todayShifts = $currentSchedule->shifts()
            ->with(['technicians'])
            ->whereDate('date', now()->format('Y-m-d'))
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'schedule' => $currentSchedule->only(['name', 'year', 'month']),
            'current_shifts' => $currentShifts,
            'today_shifts' => $todayShifts,
            'last_updated' => now()->toISOString()
        ]);
    }

    /**
     * Generate calendar data
     */
    private function generateCalendarData(WorkSchedule $schedule)
    {
        $startDate = $schedule->start_date;
        $endDate = $schedule->end_date;
        $calendar = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayShifts = $schedule->shifts()
                ->with(['technicians'])
                ->whereDate('date', $date->format('Y-m-d'))
                ->orderBy('start_time')
                ->get();

            $calendar[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->day,
                'dayName' => $date->locale('pt_BR')->dayName,
                'isWeekend' => $date->isWeekend(),
                'shifts' => $dayShifts
            ];
        }

        return $calendar;
    }
}
