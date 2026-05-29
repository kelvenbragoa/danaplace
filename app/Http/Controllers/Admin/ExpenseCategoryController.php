<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $searchQuery = request('query');
        $status = request('status');

        $categories = ExpenseCategory::query()
            ->when($searchQuery, function($query, $searchQuery) {
                $query->where(function($q) use ($searchQuery) {
                    $q->where('name', 'like', "%{$searchQuery}%")
                      ->orWhere('description', 'like', "%{$searchQuery}%");
                });
            })
            ->when($status !== null, function($query) use ($status) {
                $query->where('active', $status === 'active');
            })
            ->withCount(['expenses as total_expenses'])
            ->withSum(['expenses as total_amount'], 'amount')
            ->orderBy('name', 'asc')
            ->paginate(15);

        // Adicionar estatísticas do ano atual para cada categoria
        $currentYear = date('Y');
        $categories->getCollection()->transform(function ($category) use ($currentYear) {
            $category->current_year_expenses = $category->expenses()
                ->whereYear('expense_date', $currentYear)
                ->count();
            
            $category->current_year_amount = $category->expenses()
                ->whereYear('expense_date', $currentYear)
                ->sum('amount');

            return $category;
        });

        return $categories;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
            'active' => 'boolean'
        ]);

        $category = ExpenseCategory::create($request->all());

        return response()->json([
            'message' => 'Categoria criada com sucesso!',
            'category' => $category
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = ExpenseCategory::withCount(['expenses as total_expenses'])
            ->withSum(['expenses as total_amount'], 'amount')
            ->findOrFail($id);

        // Estatísticas detalhadas
        $currentYear = date('Y');
        $currentMonth = date('m');
        
        $stats = [
            'current_month_expenses' => $category->expensesByMonth($currentYear, $currentMonth)->count(),
            'current_month_amount' => $category->expensesByMonth($currentYear, $currentMonth)->sum('amount'),
            'current_year_expenses' => $category->expensesByYear($currentYear)->count(),
            'current_year_amount' => $category->expensesByYear($currentYear)->sum('amount'),
            'pending_expenses' => $category->expenses()->where('status', 'pending')->count(),
            'approved_expenses' => $category->expenses()->where('status', 'approved')->count(),
            'paid_expenses' => $category->expenses()->where('status', 'paid')->count(),
        ];

        // Despesas mensais do ano atual
        $monthlyExpenses = collect(range(1, 12))->map(function($month) use ($category, $currentYear) {
            return [
                'month' => $month,
                'month_name' => \Carbon\Carbon::createFromDate($currentYear, $month, 1)->format('M'),
                'total_amount' => $category->expensesByMonth($currentYear, $month)->sum('amount'),
                'expenses_count' => $category->expensesByMonth($currentYear, $month)->count()
            ];
        });

        return response()->json([
            'category' => $category,
            'statistics' => $stats,
            'monthly_expenses' => $monthlyExpenses
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = ExpenseCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $id,
            'description' => 'nullable|string',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
            'active' => 'boolean'
        ]);

        $category->update($request->all());

        return response()->json([
            'message' => 'Categoria atualizada com sucesso!',
            'category' => $category
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = ExpenseCategory::findOrFail($id);

        if ($category->expenses()->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir uma categoria que possui despesas associadas.'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Categoria removida com sucesso!'
        ]);
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(string $id)
    {
        $category = ExpenseCategory::findOrFail($id);
        
        $category->update(['active' => !$category->active]);
        
        $status = $category->active ? 'ativada' : 'desativada';

        return response()->json([
            'message' => "Categoria {$status} com sucesso!",
            'category' => $category
        ]);
    }

    /**
     * Get all active categories for dropdown
     */
    public function getActive()
    {
        $categories = ExpenseCategory::active()
            ->select('id', 'name', 'color', 'icon')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    /**
     * Get category statistics
     */
    public function statistics()
    {
        $currentYear = date('Y');
        
        $stats = [
            'total_categories' => ExpenseCategory::count(),
            'active_categories' => ExpenseCategory::active()->count(),
            'inactive_categories' => ExpenseCategory::where('active', false)->count(),
            'categories_with_expenses' => ExpenseCategory::has('expenses')->count(),
            'categories_without_expenses' => ExpenseCategory::doesntHave('expenses')->count(),
        ];

        // Top categorias por valor gasto no ano
        $topCategoriesByAmount = ExpenseCategory::with(['expenses' => function($query) use ($currentYear) {
            $query->whereYear('expense_date', $currentYear);
        }])
        ->get()
        ->map(function($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'color' => $category->color,
                'total_amount' => $category->expenses->sum('amount'),
                'expenses_count' => $category->expenses->count()
            ];
        })
        ->sortByDesc('total_amount')
        ->take(5)
        ->values();

        // Top categorias por quantidade de despesas
        $topCategoriesByCount = ExpenseCategory::with(['expenses' => function($query) use ($currentYear) {
            $query->whereYear('expense_date', $currentYear);
        }])
        ->get()
        ->map(function($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'color' => $category->color,
                'total_amount' => $category->expenses->sum('amount'),
                'expenses_count' => $category->expenses->count()
            ];
        })
        ->sortByDesc('expenses_count')
        ->take(5)
        ->values();

        return response()->json([
            'general_stats' => $stats,
            'top_categories_by_amount' => $topCategoriesByAmount,
            'top_categories_by_count' => $topCategoriesByCount
        ]);
    }

    /**
     * Get color suggestions
     */
    public function getColorSuggestions()
    {
        $colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
            '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9',
            '#F8C471', '#82E0AA', '#F1948A', '#85C1E9', '#D7BDE2',
            '#A3E4D7', '#F9E79F', '#D5A6BD', '#AED6F1', '#A9DFBF'
        ];

        return response()->json($colors);
    }

    /**
     * Get icon suggestions
     */
    public function getIconSuggestions()
    {
        $icons = [
            'fas fa-home', 'fas fa-tools', 'fas fa-bolt', 'fas fa-tint',
            'fas fa-shield-alt', 'fas fa-broom', 'fas fa-leaf', 'fas fa-car',
            'fas fa-wifi', 'fas fa-phone', 'fas fa-fire', 'fas fa-snowflake',
            'fas fa-wrench', 'fas fa-paint-brush', 'fas fa-clipboard-list',
            'fas fa-calculator', 'fas fa-file-invoice', 'fas fa-money-bill',
            'fas fa-credit-card', 'fas fa-piggy-bank'
        ];

        return response()->json($icons);
    }
}