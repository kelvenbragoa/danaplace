<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VacationPlan;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VacationPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $searchQuery = request('query');
        $status = request('status');
        $year = request('year', date('Y'));
        $technician = request('technician');

        $vacationPlans = VacationPlan::query()
            ->when($searchQuery, function($query, $searchQuery) {
                $query->whereHas('technician', function($q) use ($searchQuery) {
                    $q->where('name', 'like', "%{$searchQuery}%");
                });
            })
            ->when($status, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($year, function($query, $year) {
                $query->where('year', $year);
            })
            ->when($technician, function($query, $technician) {
                $query->where('technician_id', $technician);
            })
            ->with([
                'technician:id,name,code,department_id',
                'technician.department:id,name',
                'replacementTechnician:id,name',
                'requestedByUser',
                'approvedByUser'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate();

        return $vacationPlans;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $technicians = Technician::active()
            ->select('id', 'name', 'code', 'department_id')
            ->with('department:id,name')
            ->orderBy('name', 'asc')
            ->get();

        return [
            'technicians' => $technicians,
            'current_year' => date('Y'),
            'years' => range(date('Y'), date('Y') + 2)
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'technician_id' => 'required|exists:technicians,id',
            'year' => 'required|integer|min:' . date('Y'),
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'days_requested' => 'required|integer|min:1|max:30',
            'replacement_technician_id' => 'nullable|exists:technicians,id|different:technician_id',
            'notes' => 'nullable|string|max:500'
        ]);

        $data = $request->all();
        $data['status'] = VacationPlan::STATUS_PENDING;
        $data['requested_by'] = Auth::id();

        // Calcular dias úteis automaticamente
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $workingDays = $this->calculateWorkingDaysBetween($startDate, $endDate);

        // Validar se os dias solicitados correspondem ao período
        if ($request->days_requested > $workingDays) {
            return response()->json([
                'message' => 'Os dias solicitados excedem os dias úteis do período selecionado.',
                'working_days' => $workingDays
            ], 422);
        }

        // Verificar conflitos de férias para o mesmo técnico
        $conflict = VacationPlan::where('technician_id', $request->technician_id)
            ->where('year', $request->year)
            ->whereIn('status', [VacationPlan::STATUS_APPROVED, VacationPlan::STATUS_EXECUTED])
            ->where(function($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Já existe um plano de férias aprovado para este técnico no período selecionado.'
            ], 422);
        }

        $vacationPlan = VacationPlan::create($data);

        return response()->json([
            'message' => 'Plano de férias criado com sucesso!',
            'vacation_plan' => $vacationPlan->load(['technician', 'replacementTechnician'])
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $vacationPlan = VacationPlan::with([
            'technician:id,name,code,department_id,area_id,position',
            'technician.department:id,name',
            'technician.area:id,name',
            'replacementTechnician:id,name,code',
            'requestedByUser',
            'approvedByUser'
        ])->findOrFail($id);

        return [
            'vacation_plan' => $vacationPlan,
            'working_days' => $vacationPlan->calculateWorkingDays(),
            'is_future' => $vacationPlan->isFuture(),
            'is_active' => $vacationPlan->isActive(),
            'is_past' => $vacationPlan->isPast()
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $vacationPlan = VacationPlan::findOrFail($id);
        
        $technicians = Technician::active()
            ->select('id', 'name', 'code', 'department_id')
            ->with('department:id,name')
            ->orderBy('name', 'asc')
            ->get();

        return [
            'vacation_plan' => $vacationPlan,
            'technicians' => $technicians,
            'years' => range(date('Y'), date('Y') + 2),
            'can_edit' => $vacationPlan->status === VacationPlan::STATUS_PENDING
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $vacationPlan = VacationPlan::findOrFail($id);

        // Verificar se pode ser editado
        if ($vacationPlan->status !== VacationPlan::STATUS_PENDING) {
            return response()->json([
                'message' => 'Apenas planos pendentes podem ser editados.'
            ], 422);
        }

        $request->validate([
            'technician_id' => 'required|exists:technicians,id',
            'year' => 'required|integer|min:' . date('Y'),
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'days_requested' => 'required|integer|min:1|max:30',
            'replacement_technician_id' => 'nullable|exists:technicians,id|different:technician_id',
            'notes' => 'nullable|string|max:500'
        ]);

        // Calcular dias úteis
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $workingDays = $this->calculateWorkingDaysBetween($startDate, $endDate);

        if ($request->days_requested > $workingDays) {
            return response()->json([
                'message' => 'Os dias solicitados excedem os dias úteis do período selecionado.',
                'working_days' => $workingDays
            ], 422);
        }

        // Verificar conflitos (exceto este registro)
        $conflict = VacationPlan::where('technician_id', $request->technician_id)
            ->where('year', $request->year)
            ->where('id', '!=', $id)
            ->whereIn('status', [VacationPlan::STATUS_APPROVED, VacationPlan::STATUS_EXECUTED])
            ->where(function($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Já existe um plano de férias aprovado para este técnico no período selecionado.'
            ], 422);
        }

        $vacationPlan->update($request->all());

        return response()->json([
            'message' => 'Plano de férias atualizado com sucesso!',
            'vacation_plan' => $vacationPlan->load(['technician', 'replacementTechnician'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vacationPlan = VacationPlan::findOrFail($id);

        // Verificar se pode ser removido
        if ($vacationPlan->status === VacationPlan::STATUS_EXECUTED) {
            return response()->json([
                'message' => 'Não é possível remover um plano de férias já executado.'
            ], 422);
        }

        $vacationPlan->delete();

        return response()->json([
            'message' => 'Plano de férias removido com sucesso!'
        ]);
    }

    /**
     * Approve vacation plan
     */
    public function approve(Request $request, string $id)
    {
        $vacationPlan = VacationPlan::findOrFail($id);

        if ($vacationPlan->status !== VacationPlan::STATUS_PENDING) {
            return response()->json([
                'message' => 'Apenas planos pendentes podem ser aprovados.'
            ], 422);
        }

        $request->validate([
            'days_approved' => 'required|integer|min:1|max:' . $vacationPlan->days_requested
        ]);

        $vacationPlan->update([
            'status' => VacationPlan::STATUS_APPROVED,
            'days_approved' => $request->days_approved,
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        return response()->json([
            'message' => 'Plano de férias aprovado com sucesso!',
            'vacation_plan' => $vacationPlan->load(['technician', 'approvedByUser'])
        ]);
    }

    /**
     * Reject vacation plan
     */
    public function reject(Request $request, string $id)
    {
        $vacationPlan = VacationPlan::findOrFail($id);

        if ($vacationPlan->status !== VacationPlan::STATUS_PENDING) {
            return response()->json([
                'message' => 'Apenas planos pendentes podem ser rejeitados.'
            ], 422);
        }

        $vacationPlan->update([
            'status' => VacationPlan::STATUS_REJECTED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'notes' => $request->notes ?? $vacationPlan->notes
        ]);

        return response()->json([
            'message' => 'Plano de férias rejeitado.',
            'vacation_plan' => $vacationPlan->load(['technician', 'approvedByUser'])
        ]);
    }

    /**
     * Execute vacation plan
     */
    public function execute(string $id)
    {
        $vacationPlan = VacationPlan::findOrFail($id);

        if ($vacationPlan->status !== VacationPlan::STATUS_APPROVED) {
            return response()->json([
                'message' => 'Apenas planos aprovados podem ser executados.'
            ], 422);
        }

        $vacationPlan->update([
            'status' => VacationPlan::STATUS_EXECUTED
        ]);

        return response()->json([
            'message' => 'Plano de férias executado com sucesso!',
            'vacation_plan' => $vacationPlan
        ]);
    }

    /**
     * Get vacation statistics for dashboard
     */
    public function statistics()
    {
        $currentYear = date('Y');

        $stats = [
            'total_plans' => VacationPlan::where('year', $currentYear)->count(),
            'pending_plans' => VacationPlan::where('year', $currentYear)->where('status', VacationPlan::STATUS_PENDING)->count(),
            'approved_plans' => VacationPlan::where('year', $currentYear)->where('status', VacationPlan::STATUS_APPROVED)->count(),
            'executed_plans' => VacationPlan::where('year', $currentYear)->where('status', VacationPlan::STATUS_EXECUTED)->count(),
            'rejected_plans' => VacationPlan::where('year', $currentYear)->where('status', VacationPlan::STATUS_REJECTED)->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Calculate working days between two dates
     */
    private function calculateWorkingDaysBetween($startDate, $endDate)
    {
        $workingDays = 0;
        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            if ($current->isWeekday()) {
                $workingDays++;
            }
            $current->addDay();
        }
        
        return $workingDays;
    }
}