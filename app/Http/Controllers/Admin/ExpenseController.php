<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $searchQuery = request('query');
        $status = request('status');
        $category = request('category_id'); // Mudança aqui
        $year = request('year', date('Y'));
        $month = request('month');
        $priority = request('priority');
        $vendor = request('vendor');
        $startDate = request('date_from'); // Mudança aqui
        $endDate = request('date_to'); // Mudança aqui

        $expenses = Expense::query()
            ->when($searchQuery, function($query, $searchQuery) {
                $query->where(function($q) use ($searchQuery) {
                    $q->where('title', 'like', "%{$searchQuery}%")
                      ->orWhere('description', 'like', "%{$searchQuery}%")
                      ->orWhere('vendor_name', 'like', "%{$searchQuery}%")
                      ->orWhere('invoice_number', 'like', "%{$searchQuery}%");
                });
            })
            ->when($status, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($category, function($query, $category) {
                $query->where('expense_category_id', $category);
            })
            ->when($year, function($query, $year) {
                $query->whereYear('expense_date', $year);
            })
            ->when($month, function($query, $month) {
                $query->whereMonth('expense_date', $month);
            })
            ->when($priority, function($query, $priority) {
                $query->where('priority', $priority);
            })
            ->when($vendor, function($query, $vendor) {
                $query->where('vendor_name', 'like', "%{$vendor}%");
            })
            ->when($startDate && $endDate, function($query) use ($startDate, $endDate) {
                $query->whereBetween('expense_date', [$startDate, $endDate]);
            })
            ->with([
                'expenseCategory:id,name,color',
                'createdBy:id,firstName,lastName',
                'approvedBy:id,firstName,lastName'
            ])
            ->orderBy('expense_date', 'desc')
            ->paginate();

        // Calcular estatísticas
        $statistics = [
            'total_expenses' => Expense::count(),
            'total_amount' => Expense::sum('amount'),
            'pending_count' => Expense::where('status', Expense::STATUS_PENDING)->count(),
            'paid_count' => Expense::where('status', Expense::STATUS_PAID)->count()
        ];

        return [
            'expenses' => $expenses,
            'statistics' => $statistics
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $expenseCategories = ExpenseCategory::active()
            ->select('id', 'name', 'color', 'icon')
            ->orderBy('name', 'asc')
            ->get();

        return [
            'expense_categories' => $expenseCategories,
            'payment_methods' => $this->getPaymentMethods(),
            'priorities' => $this->getPriorities(),
            'recurring_frequencies' => $this->getRecurringFrequencies(),
            'current_year' => date('Y')
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'expense_category_id' => 'required|exists:expense_categories,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'amount' => 'required|numeric|min:0',
                // 'expense_date' => 'required|date',
                'due_date' => 'nullable|date|after_or_equal:expense_date',
                'payment_date' => 'nullable|date',
                'payment_method' => 'nullable|in:cash,bank_transfer,check,card,other',
                'vendor_name' => 'nullable|string|max:255',
                'vendor_contact' => 'nullable|string|max:255',
                'invoice_number' => 'nullable|string|max:255',
                'reference_number' => 'nullable|string|max:255',
                'priority' => 'required|in:low,medium,high,urgent',
                'recurring' => 'boolean',
                'recurring_frequency' => 'nullable|required_if:recurring,true|in:monthly,quarterly,semi_annual,annual',
                'recurring_until' => 'nullable|date|after:expense_date',
                'notes' => 'nullable|string',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif'
            ]);
    
            $data = $request->all();
            $data['expense_date'] = now();
            $data['created_by'] = Auth::id();
            $data['status'] = Expense::STATUS_PENDING;
    
            // Processar uploads de arquivos
            if ($request->hasFile('attachments')) {
                $attachmentPaths = [];
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('expense-attachments', 's3');
                    $attachmentPaths[] = $path;
                }
                $data['attachments'] = $attachmentPaths;
            }
    
            
    
            // Validar duplicação de fatura
            if ($request->invoice_number) {
                $existingExpense = Expense::where('invoice_number', $request->invoice_number)
                    ->where('vendor_name', $request->vendor_name)
                    ->exists();
    
                if ($existingExpense) {
                    return response()->json([
                        'message' => 'Já existe uma despesa com este número de fatura para este fornecedor.'
                    ], 422);
                }
            }
    
            $expense = Expense::create($data);
    
            return response()->json([
                'message' => 'Despesa criada com sucesso!',
                'expense' => $expense->load(['expenseCategory', 'createdBy'])
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error('Erro ao criar despesa: ' . $th->getMessage());
            return response()->json([
                'message' => 'Erro ao criar despesa: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $expense = Expense::with([
            'expenseCategory:id,name,color,icon',
            'createdBy:id,firstName,lastName',
            'approvedBy:id,firstName,lastName'
        ])->findOrFail($id);

        

        // Gerar URLs temporárias para anexos
        if ($expense->attachments) {
            $attachmentUrls = [];
            foreach ($expense->attachments as $attachment) {
                try {
                    // Verificar se o arquivo existe antes de tentar gerar URL e obter tamanho
                    if (Storage::disk('s3')->exists($attachment)) {
                        $url = Storage::disk('s3')->temporaryUrl(
                            $attachment, 
                            now()->addMinutes(10), 
                            ['ResponseContentDisposition' => 'attachment']
                        );
                        
                        // Tentar obter tamanho com tratamento de erro
                        $fileSize = 0;
                        try {
                            $fileSize = Storage::disk('s3')->size($attachment);
                        } catch (\Exception $e) {
                            // Se não conseguir obter o tamanho, usar 0
                            $fileSize = 0;
                        }
                        
                        $attachmentUrls[] = [
                            'path' => $attachment,
                            'url' => $url,
                            'name' => basename($attachment),
                            'size' => $fileSize,
                            'exists' => true
                        ];
                    } else {
                        // Arquivo não existe mais no S3
                        $attachmentUrls[] = [
                            'path' => $attachment,
                            'url' => null,
                            'name' => basename($attachment),
                            'size' => 0,
                            'exists' => false,
                            'error' => 'Arquivo não encontrado'
                        ];
                    }
                } catch (\Exception $e) {
                    // Erro geral ao processar anexo
                    $attachmentUrls[] = [
                        'path' => $attachment,
                        'url' => null,
                        'name' => basename($attachment),
                        'size' => 0,
                        'exists' => false,
                        'error' => 'Erro ao processar anexo: ' . $e->getMessage()
                    ];
                }
            }
            $expense->attachment_details = $attachmentUrls;
        }

        return [
            'expense' => $expense,
            'can_edit' => $this->canEdit($expense),
            'can_approve' => $this->canApprove($expense),
            'can_pay' => $this->canPay($expense),
            'is_overdue' => $expense->isOverdue()
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $expense = Expense::findOrFail($id);
        
        if (!$this->canEdit($expense)) {
            return response()->json([
                'message' => 'Esta despesa não pode ser editada.'
            ], 422);
        }

        $expenseCategories = ExpenseCategory::active()
            ->select('id', 'name', 'color', 'icon')
            ->orderBy('name', 'asc')
            ->get();

        return [
            'expense' => $expense,
            'expense_categories' => $expenseCategories,
            'payment_methods' => $this->getPaymentMethods(),
            'priorities' => $this->getPriorities(),
            'recurring_frequencies' => $this->getRecurringFrequencies(),
            'can_edit' => true
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $expense = Expense::findOrFail($id);

        if (!$this->canEdit($expense)) {
            return response()->json([
                'message' => 'Esta despesa não pode ser editada.'
            ], 422);
        }

        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:expense_date',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|in:cash,bank_transfer,check,card,other',
            'vendor_name' => 'nullable|string|max:255',
            'vendor_contact' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'priority' => 'required|in:low,medium,high,urgent',
            'recurring' => 'nullable|boolean',
            'recurring_frequency' => 'nullable|required_if:recurring,true|in:monthly,quarterly,semi_annual,annual',
            'recurring_until' => 'nullable|date|after:expense_date',
            'notes' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif'
        ]);

        $data = $request->except(['attachments']);

        // Processar novos uploads
        if ($request->hasFile('attachments')) {
            $attachmentPaths = $expense->attachments ?? [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('expense-attachments', 's3');
                $attachmentPaths[] = $path;
            }
            $data['attachments'] = $attachmentPaths;
        }

        // Validar duplicação de fatura (exceto este registro)
        if ($request->invoice_number) {
            $existingExpense = Expense::where('invoice_number', $request->invoice_number)
                ->where('vendor_name', $request->vendor_name)
                ->where('id', '!=', $id)
                ->exists();

            if ($existingExpense) {
                return response()->json([
                    'message' => 'Já existe uma despesa com este número de fatura para este fornecedor.'
                ], 422);
            }
        }

        $expense->update($data);

        return response()->json([
            'message' => 'Despesa atualizada com sucesso!',
            'expense' => $expense->load(['expenseCategory', 'createdBy'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status === Expense::STATUS_PAID) {
            return response()->json([
                'message' => 'Não é possível remover uma despesa já paga.'
            ], 422);
        }

        // Remover arquivos anexos
        if ($expense->attachments) {
            foreach ($expense->attachments as $attachment) {
                Storage::disk('s3')->delete($attachment);
            }
        }

        $expense->delete();

        return response()->json([
            'message' => 'Despesa removida com sucesso!'
        ]);
    }

    /**
     * Approve expense
     */
    public function approve(string $id)
    {
        $expense = Expense::findOrFail($id);

        if (!$this->canApprove($expense)) {
            return response()->json([
                'message' => 'Esta despesa não pode ser aprovada.'
            ], 422);
        }

        $expense->update([
            'status' => Expense::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        return response()->json([
            'message' => 'Despesa aprovada com sucesso!',
            'expense' => $expense->load(['expenseCategory', 'approvedBy'])
        ]);
    }

    /**
     * Reject expense
     */
    public function reject(Request $request, string $id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status !== Expense::STATUS_PENDING) {
            return response()->json([
                'message' => 'Apenas despesas pendentes podem ser rejeitadas.'
            ], 422);
        }

        $expense->update([
            'status' => Expense::STATUS_REJECTED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'notes' => $request->rejection_reason ?? $request->notes ?? $expense->notes
        ]);

        return response()->json([
            'message' => 'Despesa rejeitada.',
            'expense' => $expense->load(['expenseCategory', 'approvedBy'])
        ]);
    }

    /**
     * Mark expense as paid (alias for markAsPaid)
     */
    public function pay(Request $request, string $id)
    {
        return $this->markAsPaid($request, $id);
    }

    /**
     * Mark expense as paid
     */
    public function markAsPaid(Request $request, string $id)
    {
        $expense = Expense::findOrFail($id);

        if (!$this->canPay($expense)) {
            return response()->json([
                'message' => 'Esta despesa não pode ser marcada como paga.'
            ], 422);
        }

        $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,check,card,other',
            'reference_number' => 'nullable|string|max:255',
            'payment_notes' => 'nullable|string'
        ]);

        $expense->update([
            'status' => Expense::STATUS_PAID,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'reference_number' => $request->reference_number ?? $expense->reference_number,
            'notes' => $request->payment_notes ? ($expense->notes . '\n\nPagamento: ' . $request->payment_notes) : $expense->notes
        ]);

        return response()->json([
            'message' => 'Despesa marcada como paga com sucesso!',
            'expense' => $expense
        ]);
    }

    /**
     * Get expense statistics
     */
    public function statistics()
    {
        $currentYear = date('Y');
        $currentMonth = date('m');

        $stats = [
            // Estatísticas gerais
            'total_expenses_current_month' => Expense::byMonth($currentYear, $currentMonth)->sum('amount'),
            'total_expenses_current_year' => Expense::byYear($currentYear)->sum('amount'),
            'pending_expenses_count' => Expense::where('status', Expense::STATUS_PENDING)->count(),
            'overdue_expenses_count' => Expense::overdue()->count(),
            'paid_expenses_current_month' => Expense::byMonth($currentYear, $currentMonth)->where('status', Expense::STATUS_PAID)->sum('amount'),
            
            // Por status
            'expenses_by_status' => Expense::selectRaw('status, COUNT(*) as count, SUM(amount) as total')
                ->whereYear('expense_date', $currentYear)
                ->groupBy('status')
                ->get(),
            
            // Por categoria
            'expenses_by_category' => ExpenseCategory::with(['expenses' => function($query) use ($currentYear) {
                $query->whereYear('expense_date', $currentYear);
            }])
            ->get()
            ->map(function($category) {
                return [
                    'category_name' => $category->name,
                    'category_color' => $category->color,
                    'total_amount' => $category->expenses->sum('amount'),
                    'expenses_count' => $category->expenses->count()
                ];
            }),
            
            // Despesas mensais do ano atual
            'monthly_expenses' => collect(range(1, 12))->map(function($month) use ($currentYear) {
                return [
                    'month' => $month,
                    'month_name' => Carbon::createFromDate($currentYear, $month, 1)->format('M'),
                    'total_amount' => Expense::byMonth($currentYear, $month)->sum('amount'),
                    'expenses_count' => Expense::byMonth($currentYear, $month)->count()
                ];
            }),
            
            // Top fornecedores
            'top_vendors' => Expense::selectRaw('vendor_name, COUNT(*) as expenses_count, SUM(amount) as total_amount')
                ->whereYear('expense_date', $currentYear)
                ->whereNotNull('vendor_name')
                ->groupBy('vendor_name')
                ->orderByDesc('total_amount')
                ->limit(5)
                ->get()
        ];

        return response()->json($stats);
    }

    /**
     * Remove attachment
     */
    public function removeAttachment(Request $request, string $id)
    {
        $expense = Expense::findOrFail($id);
        
        if (!$this->canEdit($expense)) {
            return response()->json([
                'message' => 'Não é possível remover anexos desta despesa.'
            ], 422);
        }

        $attachmentPath = $request->attachment_path;
        
        if ($expense->attachments && in_array($attachmentPath, $expense->attachments)) {
            // Remover do array
            $attachments = array_filter($expense->attachments, function($path) use ($attachmentPath) {
                return $path !== $attachmentPath;
            });
            
            $expense->update(['attachments' => array_values($attachments)]);
            
            // Remover arquivo físico do S3
            try {
                Storage::disk('s3')->delete($attachmentPath);
            } catch (\Exception $e) {
                // Log do erro mas não falha a operação
                Log::warning('Erro ao remover arquivo do S3: ' . $e->getMessage());
            }
            
            return response()->json(['message' => 'Anexo removido com sucesso!']);
        }

        return response()->json(['message' => 'Anexo não encontrado.'], 404);
    }

    /**
     * Get filter options
     */
    public function getFilterOptions()
    {
        $categories = ExpenseCategory::active()
            ->select('id', 'name', 'color')
            ->orderBy('name')
            ->get();

        $vendors = Expense::selectRaw('vendor_name')
            ->whereNotNull('vendor_name')
            ->distinct()
            ->orderBy('vendor_name')
            ->pluck('vendor_name');

        $years = Expense::selectRaw('YEAR(expense_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return response()->json([
            'categories' => $categories,
            'vendors' => $vendors,
            'years' => $years,
            'statuses' => [
                ['value' => 'pending', 'label' => 'Pendente'],
                ['value' => 'approved', 'label' => 'Aprovada'],
                ['value' => 'paid', 'label' => 'Paga'],
                ['value' => 'rejected', 'label' => 'Rejeitada'],
                ['value' => 'overdue', 'label' => 'Vencida']
            ],
            'priorities' => $this->getPriorities(),
            'payment_methods' => $this->getPaymentMethods()
        ]);
    }

    /**
     * Check if expense can be edited
     */
    private function canEdit(Expense $expense)
    {
        return in_array($expense->status, [Expense::STATUS_PENDING, Expense::STATUS_REJECTED]);
    }

    /**
     * Check if expense can be approved
     */
    private function canApprove(Expense $expense)
    {
        return $expense->status === Expense::STATUS_PENDING;
    }

    /**
     * Check if expense can be paid
     */
    private function canPay(Expense $expense)
    {
        return $expense->status === Expense::STATUS_APPROVED;
    }

    /**
     * Get payment methods options
     */
    private function getPaymentMethods()
    {
        return [
            ['value' => 'cash', 'label' => 'Dinheiro'],
            ['value' => 'bank_transfer', 'label' => 'Transferência Bancária'],
            ['value' => 'check', 'label' => 'Cheque'],
            ['value' => 'card', 'label' => 'Cartão'],
            ['value' => 'other', 'label' => 'Outro']
        ];
    }

    /**
     * Get priorities options
     */
    private function getPriorities()
    {
        return [
            ['value' => 'low', 'label' => 'Baixa'],
            ['value' => 'medium', 'label' => 'Média'],
            ['value' => 'high', 'label' => 'Alta'],
            ['value' => 'urgent', 'label' => 'Urgente']
        ];
    }

    /**
     * Get recurring frequencies options
     */
    private function getRecurringFrequencies()
    {
        return [
            ['value' => 'monthly', 'label' => 'Mensal'],
            ['value' => 'quarterly', 'label' => 'Trimestral'],
            ['value' => 'semi_annual', 'label' => 'Semestral'],
            ['value' => 'annual', 'label' => 'Anual']
        ];
    }
}