<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EggModule\EggExpense;
use App\Models\Technician;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EggExpenseController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $expenses = EggExpense::query()
            ->with(['farm', 'house', 'flock.house', 'createdBy'])
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('title', 'like', "%{$searchQuery}%")
                        ->orWhere('vendor_name', 'like', "%{$searchQuery}%")
                        ->orWhere('invoice_number', 'like', "%{$searchQuery}%")
                        ->orWhere('description', 'like', "%{$searchQuery}%");
                });
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category', $request->category);
            })
            ->when($request->filled('farm_id'), function ($query) use ($request) {
                $query->where('farm_id', $request->farm_id);
            })
            ->when($request->filled('house_id'), function ($query) use ($request) {
                $query->where('house_id', $request->house_id);
            })
            ->when($request->filled('flock_id'), function ($query) use ($request) {
                $query->where('flock_id', $request->flock_id);
            })
            ->when($request->filled('start_date'), function ($query) use ($request) {
                $query->whereDate('expense_date', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                $query->whereDate('expense_date', '<=', $request->end_date);
            })
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json($expenses);
    }

    public function meta()
    {
        return response()->json([
            'categories' => EggExpense::categories(),
            'payment_methods' => EggExpense::paymentMethods(),
        ]);
    }

    public function summary(Request $request)
    {
        $query = EggExpense::query()
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('farm_id'), fn ($q) => $q->where('farm_id', $request->farm_id))
            ->when($request->filled('flock_id'), fn ($q) => $q->where('flock_id', $request->flock_id))
            ->when($request->filled('start_date'), fn ($q) => $q->whereDate('expense_date', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->whereDate('expense_date', '<=', $request->end_date));

        $byCategory = (clone $query)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return response()->json([
            'total' => round((clone $query)->sum('amount'), 2),
            'count' => (clone $query)->count(),
            'by_category' => $byCategory,
        ]);
    }

    public function dashboard(Request $request)
    {
        [$startDate, $endDate] = $this->resolvePeriod($request);

        $expenseQuery = EggExpense::query()
            ->whereDate('expense_date', '>=', $startDate)
            ->whereDate('expense_date', '<=', $endDate)
            ->when($request->filled('farm_id'), fn ($q) => $q->where('farm_id', $request->farm_id));

        $byCategoryRaw = (clone $expenseQuery)
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->get()
            ->keyBy('category');

        $categories = EggExpense::categories();
        $byCategory = collect($categories)->map(function ($label, $key) use ($byCategoryRaw) {
            $row = $byCategoryRaw->get($key);
            return [
                'key' => $key,
                'label' => $label,
                'total' => round((float) ($row->total ?? 0), 2),
                'count' => (int) ($row->count ?? 0),
            ];
        })->values();

        $operationalTotal = round((clone $expenseQuery)->sum('amount'), 2);
        $expenseCount = (clone $expenseQuery)->count();

        $byMonth = (clone $expenseQuery)
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'month' => $row->month,
                'total' => round((float) $row->total, 2),
            ]);

        $byFarm = (clone $expenseQuery)
            ->leftJoin('farms', 'farms.id', '=', 'egg_expenses.farm_id')
            ->selectRaw('egg_expenses.farm_id, COALESCE(farms.name, "Sem granja") as farm_name, SUM(egg_expenses.amount) as total')
            ->groupBy('egg_expenses.farm_id', 'farms.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'farm_id' => $row->farm_id,
                'farm_name' => $row->farm_name,
                'total' => round((float) $row->total, 2),
            ]);

        $recent = (clone $expenseQuery)
            ->with(['farm:id,name', 'flock:id,code'])
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'title', 'amount', 'expense_date', 'category', 'farm_id', 'flock_id', 'vendor_name']);

        $departments = Department::orderBy('name')->get(['id', 'name']);
        $defaultDepartment = $departments->first(function ($dept) {
            $name = mb_strtolower($dept->name);
            return str_contains($name, 'avíc') || str_contains($name, 'avic') || str_contains($name, 'ovo');
        }) ?? $departments->first();

        $departmentIdParam = $request->get('department_id');
        $departmentId = null;

        if ($departmentIdParam === 'all' || $departmentIdParam === '') {
            $departmentId = $departmentIdParam === 'all' ? null : ($defaultDepartment?->id);
        } elseif ($request->filled('department_id')) {
            $departmentId = (int) $request->department_id;
        } else {
            $departmentId = $defaultDepartment?->id;
        }

        $technicians = Technician::query()
            ->with('department:id,name')
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'position', 'salary', 'department_id']);

        $monthlyPayroll = round((float) $technicians->sum('salary'), 2);
        $monthsInPeriod = max(1, round(Carbon::parse($startDate)->floatDiffInMonths(Carbon::parse($endDate)->endOfDay()), 1));
        $salaryCost = round($monthlyPayroll * $monthsInPeriod, 2);

        $department = $departmentId
            ? $departments->firstWhere('id', $departmentId)
            : null;

        $pieLabels = $byCategory->filter(fn ($c) => $c['total'] > 0)->pluck('label')->values();
        $pieData = $byCategory->filter(fn ($c) => $c['total'] > 0)->pluck('total')->values();

        if ($salaryCost > 0) {
            $pieLabels->push('Salários técnicos');
            $pieData->push($salaryCost);
        }

        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'months' => $monthsInPeriod,
            ],
            'summary' => [
                'operational_total' => $operationalTotal,
                'expense_count' => $expenseCount,
                'monthly_payroll' => $monthlyPayroll,
                'salary_cost' => $salaryCost,
                'combined_total' => round($operationalTotal + $salaryCost, 2),
                'technician_count' => $technicians->count(),
            ],
            'by_category' => $byCategory,
            'by_month' => $byMonth,
            'by_farm' => $byFarm,
            'recent_expenses' => $recent,
            'technicians' => $technicians,
            'departments' => $departments,
            'selected_department' => $department,
            'charts' => [
                'pie' => [
                    'labels' => $pieLabels,
                    'data' => $pieData,
                ],
                'bar_categories' => [
                    'labels' => $byCategory->pluck('label'),
                    'data' => $byCategory->pluck('total'),
                ],
                'bar_months' => [
                    'labels' => $byMonth->pluck('month'),
                    'data' => $byMonth->pluck('total'),
                ],
            ],
        ]);
    }

    private function resolvePeriod(Request $request): array
    {
        $period = $request->get('period', 'month');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            return [
                Carbon::parse($request->start_date)->toDateString(),
                Carbon::parse($request->end_date)->toDateString(),
            ];
        }

        $end = Carbon::now()->toDateString();

        return match ($period) {
            'week' => [Carbon::now()->subDays(6)->toDateString(), $end],
            'year' => [Carbon::now()->startOfYear()->toDateString(), $end],
            'yeartodate' => [Carbon::now()->startOfYear()->toDateString(), $end],
            'monthtodate' => [Carbon::now()->startOfMonth()->toDateString(), $end],
            '30' => [Carbon::now()->subDays(29)->toDateString(), $end],
            '90' => [Carbon::now()->subDays(89)->toDateString(), $end],
            default => [Carbon::now()->startOfMonth()->toDateString(), $end],
        };
    }

    public function show(EggExpense $eggExpense)
    {
        return response()->json(
            $eggExpense->load(['farm', 'house.farm', 'flock.house.farm', 'createdBy'])
        );
    }

    public function store(Request $request)
    {
        $validated = $this->validateExpense($request);
        $validated['created_by'] = auth()->id();

        $expense = EggExpense::create($validated);

        return response()->json(
            $expense->load(['farm', 'house', 'flock', 'createdBy']),
            201
        );
    }

    public function update(Request $request, EggExpense $eggExpense)
    {
        $validated = $this->validateExpense($request);
        $eggExpense->update($validated);

        return response()->json(
            $eggExpense->fresh()->load(['farm', 'house', 'flock', 'createdBy'])
        );
    }

    public function destroy(EggExpense $eggExpense)
    {
        $eggExpense->delete();

        return response()->json(['message' => 'Despesa apagada com sucesso']);
    }

    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'category' => 'required|in:' . implode(',', array_keys(EggExpense::categories())),
            'farm_id' => 'nullable|exists:farms,id',
            'house_id' => 'nullable|exists:houses,id',
            'flock_id' => 'nullable|exists:flocks,id',
            'vendor_name' => 'nullable|string|max:150',
            'invoice_number' => 'nullable|string|max:100',
            'payment_method' => 'nullable|in:' . implode(',', array_keys(EggExpense::paymentMethods())),
            'notes' => 'nullable|string',
        ]);
    }
}
