<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalaryProcess;
use App\Models\SalaryProcessItem;
use App\Models\Technician;
use App\Models\TechnicianAbsence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class SalaryProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $searchQuery = request('query');

        $salaryProcesses = SalaryProcess::query()
            ->when(request('query'), function($query, $searchQuery) {
                $query->where('title', 'like', "%{$searchQuery}%")
                      ->orWhere('month', 'like', "%{$searchQuery}%")
                      ->orWhere('year', 'like', "%{$searchQuery}%");
            })
            ->with('processedByUser')
            ->with('approvedByUser')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate();

        return $salaryProcesses;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $technicians = Technician::active()
            ->with('department', 'area')
            ->orderBy('name', 'asc')
            ->get();

        return [
            'technicians' => $technicians
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2050',
            'technicians' => 'required|array|min:1',
            'technicians.*.technician_id' => 'required|exists:technicians,id',
            'technicians.*.base_salary' => 'required|numeric|min:0',
            'technicians.*.overtime_hours' => 'nullable|numeric|min:0',
            'technicians.*.bonus' => 'nullable|numeric|min:0',
            'technicians.*.deductions' => 'nullable|numeric|min:0',
            'technicians.*.observations' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar se já existe um processo para este mês/ano
        $existingProcess = SalaryProcess::where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if ($existingProcess) {
            return response()->json([
                'error' => 'Já existe um processo salarial para este mês/ano.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $salaryProcess = SalaryProcess::create([
                'title' => $request->title,
                'description' => $request->description,
                'month' => $request->month,
                'year' => $request->year,
                'status' => 'pending',
                'processed_by_user_id' => Auth::id(),
                'processed_at' => now()
            ]);

            $totalAmount = 0;
            $totalTechnicians = 0;

            foreach ($request->technicians as $techData) {
                $technician = Technician::find($techData['technician_id']);
                
                $overtimeAmount = 0;
                if (isset($techData['overtime_hours']) && $techData['overtime_hours'] > 0) {
                    $overtimeRate = $technician->overtime_rate ?? ($techData['base_salary'] / 160 * 1.5); // 1.5x rate padrão
                    $overtimeAmount = $techData['overtime_hours'] * $overtimeRate;
                }

                $bonus = $techData['bonus'] ?? 0;
                $deductions = $techData['deductions'] ?? 0;
                
                // Calcular deduções por faltas aprovadas
                $absenceDeductions = $this->calculateAbsenceDeductions(
                    $techData['technician_id'], 
                    $request->month, 
                    $request->year, 
                    $techData['base_salary']
                );
                
                $totalDeductions = $deductions + $absenceDeductions;
                $netSalary = ($techData['base_salary'] + $overtimeAmount + $bonus) - $totalDeductions;

                SalaryProcessItem::create([
                    'salary_process_id' => $salaryProcess->id,
                    'technician_id' => $techData['technician_id'],
                    'base_salary' => $techData['base_salary'],
                    'overtime_hours' => $techData['overtime_hours'] ?? 0,
                    'overtime_amount' => $overtimeAmount,
                    'bonus' => $bonus,
                    'deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                    'observations' => $techData['observations'] ?? null
                ]);

                $totalAmount += $netSalary;
                $totalTechnicians++;
            }

            // Atualizar totais
            $salaryProcess->update([
                'total_amount' => $totalAmount,
                'total_technicians' => $totalTechnicians,
                'status' => 'processed'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Processo salarial criado com sucesso!',
                'salary_process' => $salaryProcess
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao processar folha salarial: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $salaryProcess = SalaryProcess::with([
            'items.technician.department',
            'items.technician.area',
            'processedByUser',
            'approvedByUser'
        ])->findOrFail($id);

        return [
            'salary_process' => $salaryProcess
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $salaryProcess = SalaryProcess::with([
            'items.technician.department',
            'items.technician.area'
        ])->findOrFail($id);

        $technicians = Technician::active()
            ->with('department', 'area')
            ->orderBy('name', 'asc')
            ->get();

        return [
            'salary_process' => $salaryProcess,
            'technicians' => $technicians
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $salaryProcess = SalaryProcess::findOrFail($id);

        if ($salaryProcess->status === 'approved' || $salaryProcess->status === 'paid') {
            return response()->json(['error' => 'Não é possível editar um processo já aprovado ou pago.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'technicians' => 'required|array|min:1',
            'technicians.*.technician_id' => 'required|exists:technicians,id',
            'technicians.*.base_salary' => 'required|numeric|min:0',
            'technicians.*.overtime_hours' => 'nullable|numeric|min:0',
            'technicians.*.bonus' => 'nullable|numeric|min:0',
            'technicians.*.deductions' => 'nullable|numeric|min:0',
            'technicians.*.observations' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $salaryProcess->update([
                'title' => $request->title,
                'description' => $request->description
            ]);

            // Remover itens existentes
            $salaryProcess->items()->delete();

            $totalAmount = 0;
            $totalTechnicians = 0;

            // Recriar itens
            foreach ($request->technicians as $techData) {
                $technician = Technician::find($techData['technician_id']);
                
                $overtimeAmount = 0;
                if (isset($techData['overtime_hours']) && $techData['overtime_hours'] > 0) {
                    $overtimeRate = $technician->overtime_rate ?? ($techData['base_salary'] / 160 * 1.5);
                    $overtimeAmount = $techData['overtime_hours'] * $overtimeRate;
                }

                $bonus = $techData['bonus'] ?? 0;
                $deductions = $techData['deductions'] ?? 0;
                
                // Calcular deduções por faltas aprovadas
                $absenceDeductions = $this->calculateAbsenceDeductions(
                    $techData['technician_id'], 
                    $salaryProcess->month, 
                    $salaryProcess->year, 
                    $techData['base_salary']
                );
                
                $totalDeductions = $deductions + $absenceDeductions;
                $netSalary = ($techData['base_salary'] + $overtimeAmount + $bonus) - $totalDeductions;

                SalaryProcessItem::create([
                    'salary_process_id' => $salaryProcess->id,
                    'technician_id' => $techData['technician_id'],
                    'base_salary' => $techData['base_salary'],
                    'overtime_hours' => $techData['overtime_hours'] ?? 0,
                    'overtime_amount' => $overtimeAmount,
                    'bonus' => $bonus,
                    'deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                    'observations' => $techData['observations'] ?? null
                ]);

                $totalAmount += $netSalary;
                $totalTechnicians++;
            }

            // Atualizar totais
            $salaryProcess->update([
                'total_amount' => $totalAmount,
                'total_technicians' => $totalTechnicians
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Processo salarial atualizado com sucesso!',
                'salary_process' => $salaryProcess
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao atualizar folha salarial: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $salaryProcess = SalaryProcess::findOrFail($id);

        if ($salaryProcess->status === 'approved' || $salaryProcess->status === 'paid') {
            return response()->json(['error' => 'Não é possível excluir um processo já aprovado ou pago.'], 422);
        }

        $salaryProcess->delete();

        return response()->json(['message' => 'Processo salarial excluído com sucesso!']);
    }

    /**
     * Aprovar processo salarial
     */
    public function approve(string $id)
    {
        $salaryProcess = SalaryProcess::findOrFail($id);

        if ($salaryProcess->status !== 'processed') {
            return response()->json(['error' => 'Só é possível aprovar processos que foram processados.'], 422);
        }

        $salaryProcess->update([
            'status' => 'approved',
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now()
        ]);

        return response()->json(['message' => 'Processo salarial aprovado com sucesso!']);
    }

    /**
     * Marcar como pago
     */
    public function markAsPaid(string $id)
    {
        $salaryProcess = SalaryProcess::findOrFail($id);

        if ($salaryProcess->status !== 'approved') {
            return response()->json(['error' => 'Só é possível marcar como pago processos que foram aprovados.'], 422);
        }

        $salaryProcess->update([
            'status' => 'paid'
        ]);

        return response()->json(['message' => 'Processo salarial marcado como pago!']);
    }

    /**
     * Gerar relatório da folha salarial
     */
    public function generateReport(string $id)
    {
        $salaryProcess = SalaryProcess::with([
            'items.technician.department',
            'items.technician.area',
            'processedByUser',
            'approvedByUser'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('admin.salary-processes.report', compact('salaryProcess'));
        
        $filename = "folha-salarial-{$salaryProcess->month}-{$salaryProcess->year}.pdf";
        
        return $pdf->download($filename);
    }

    /**
     * Gerar payslip individual
     */
    public function generatePayslip(string $processId, string $itemId)
    {
        $salaryProcess = SalaryProcess::with([
            'processedByUser',
            'approvedByUser'
        ])->findOrFail($processId);

        $item = SalaryProcessItem::with([
            'technician.department',
            'technician.area'
        ])->where('salary_process_id', $processId)->findOrFail($itemId);

        $pdf = Pdf::loadView('admin.salary-processes.payslip', compact('salaryProcess', 'item'));
        
        $filename = "payslip-{$item->technician->name}-{$salaryProcess->month}-{$salaryProcess->year}.pdf";
        $filename = str_replace(' ', '-', $filename);
        $filename = preg_replace('/[^A-Za-z0-9\-.]/', '', $filename);
        
        return $pdf->download($filename);
    }

    /**
     * Obter técnicos ativos com salários
     */
    public function getTechnicians()
    {
        $technicians = Technician::active()
            ->with('department', 'area')
            ->whereNotNull('salary')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json(['technicians' => $technicians]);
    }

    /**
     * Calcular deduções por faltas aprovadas
     */
    private function calculateAbsenceDeductions($technicianId, $month, $year, $baseSalary)
    {
        // Buscar faltas aprovadas para o técnico no período
        $absences = TechnicianAbsence::approved()
                                   ->forTechnician($technicianId)
                                   ->forMonth($month, $year)
                                   ->get();
        
        if ($absences->isEmpty()) {
            return 0;
        }
        
        // Calcular valor por hora baseado no salário base
        // Assumindo 160 horas por mês (8h/dia x 20 dias úteis)
        $hourlyRate = $baseSalary / 160;
        
        // Somar todas as horas perdidas e multiplicar pela taxa horária
        $totalHoursLost = $absences->sum('hours_lost');
        
        return $totalHoursLost * $hourlyRate;
    }

    /**
     * Obter detalhes das faltas para um técnico em um período
     */
    public function getTechnicianAbsences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'technician_id' => 'required|exists:technicians,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2050'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $absences = TechnicianAbsence::approved()
                                   ->forTechnician($request->technician_id)
                                   ->forMonth($request->month, $request->year)
                                   ->orderBy('date', 'asc')
                                   ->get();

        $technician = Technician::with('department')->find($request->technician_id);
        
        // Calcular totais
        $totalHoursLost = $absences->sum('hours_lost');
        $hourlyRate = $technician->salary ? ($technician->salary / 160) : 0;
        $totalDeduction = $totalHoursLost * $hourlyRate;

        return response()->json([
            'technician' => $technician,
            'absences' => $absences,
            'summary' => [
                'total_absences' => $absences->count(),
                'total_hours_lost' => $totalHoursLost,
                'hourly_rate' => $hourlyRate,
                'total_deduction' => $totalDeduction,
                'period' => [
                    'month' => $request->month,
                    'year' => $request->year
                ]
            ]
        ]);
    }
}
