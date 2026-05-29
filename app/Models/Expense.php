<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_category_id',
        'title',
        'description',
        'amount',
        'expense_date',
        'due_date',
        'payment_date',
        'payment_method',
        'vendor_name',
        'vendor_contact',
        'invoice_number',
        'reference_number',
        'status',
        'priority',
        'recurring',
        'recurring_frequency',
        'recurring_until',
        'notes',
        'attachments',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'recurring' => 'boolean',
        'recurring_until' => 'date',
        'approved_at' => 'datetime',
        'attachments' => 'array'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_PAID = 'paid';
    const STATUS_REJECTED = 'rejected';
    const STATUS_OVERDUE = 'overdue';

    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Payment methods
    const PAYMENT_CASH = 'cash';
    const PAYMENT_BANK_TRANSFER = 'bank_transfer';
    const PAYMENT_CHECK = 'check';
    const PAYMENT_CARD = 'card';
    const PAYMENT_OTHER = 'other';

    // Recurring frequencies
    const RECURRING_MONTHLY = 'monthly';
    const RECURRING_QUARTERLY = 'quarterly';
    const RECURRING_SEMI_ANNUAL = 'semi_annual';
    const RECURRING_ANNUAL = 'annual';

    /**
     * Relacionamento com categoria
     */
    public function expenseCategory()
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    /**
     * Relacionamento com usuário criador
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relacionamento com usuário aprovador
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope para filtrar por status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar por categoria
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('expense_category_id', $categoryId);
    }

    /**
     * Scope para filtrar por mês
     */
    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('expense_date', $year)
                    ->whereMonth('expense_date', $month);
    }

    /**
     * Scope para filtrar por ano
     */
    public function scopeByYear($query, $year)
    {
        return $query->whereYear('expense_date', $year);
    }

    /**
     * Scope para filtrar por período
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    /**
     * Scope para despesas vencidas
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', Carbon::now())
                    ->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    /**
     * Scope para despesas recorrentes
     */
    public function scopeRecurring($query)
    {
        return $query->where('recurring', true);
    }

    /**
     * Verificar se a despesa está vencida
     */
    public function isOverdue()
    {
        if (!$this->due_date) return false;
        
        return Carbon::parse($this->due_date)->isPast() && 
               in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    /**
     * Verificar se a despesa foi paga
     */
    public function isPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Obter dias até o vencimento
     */
    public function getDaysUntilDueAttribute()
    {
        if (!$this->due_date) return null;
        
        return Carbon::now()->diffInDays(Carbon::parse($this->due_date), false);
    }

    /**
     * Obter status formatado
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_APPROVED => 'Aprovada',
            self::STATUS_PAID => 'Paga',
            self::STATUS_REJECTED => 'Rejeitada',
            self::STATUS_OVERDUE => 'Vencida'
        ];

        // Verificar se está vencida automaticamente
        if ($this->isOverdue()) {
            return 'Vencida';
        }

        return $labels[$this->status] ?? 'Desconhecido';
    }

    /**
     * Obter cor do status
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'info',
            self::STATUS_PAID => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_OVERDUE => 'danger'
        ];

        // Verificar se está vencida automaticamente
        if ($this->isOverdue()) {
            return 'danger';
        }

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Obter prioridade formatada
     */
    public function getPriorityLabelAttribute()
    {
        $labels = [
            self::PRIORITY_LOW => 'Baixa',
            self::PRIORITY_MEDIUM => 'Média',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_URGENT => 'Urgente'
        ];

        return $labels[$this->priority] ?? 'Média';
    }

    /**
     * Obter cor da prioridade
     */
    public function getPriorityColorAttribute()
    {
        $colors = [
            self::PRIORITY_LOW => 'success',
            self::PRIORITY_MEDIUM => 'info',
            self::PRIORITY_HIGH => 'warning',
            self::PRIORITY_URGENT => 'danger'
        ];

        return $colors[$this->priority] ?? 'info';
    }

    /**
     * Obter método de pagamento formatado
     */
    public function getPaymentMethodLabelAttribute()
    {
        $labels = [
            self::PAYMENT_CASH => 'Dinheiro',
            self::PAYMENT_BANK_TRANSFER => 'Transferência Bancária',
            self::PAYMENT_CHECK => 'Cheque',
            self::PAYMENT_CARD => 'Cartão',
            self::PAYMENT_OTHER => 'Outro'
        ];

        return $labels[$this->payment_method] ?? '-';
    }

    /**
     * Obter URLs dos anexos
     */
    public function getAttachmentUrlsAttribute()
    {
        if (!$this->attachments || !is_array($this->attachments)) {
            return [];
        }

        return array_map(function($attachment) {
            return Storage::url($attachment);
        }, $this->attachments);
    }

    /**
     * Gerar próxima despesa recorrente
     */
    public function generateNextRecurringExpense()
    {
        if (!$this->recurring || !$this->recurring_frequency) {
            return null;
        }

        $nextDate = Carbon::parse($this->expense_date);

        switch ($this->recurring_frequency) {
            case self::RECURRING_MONTHLY:
                $nextDate->addMonth();
                break;
            case self::RECURRING_QUARTERLY:
                $nextDate->addMonths(3);
                break;
            case self::RECURRING_SEMI_ANNUAL:
                $nextDate->addMonths(6);
                break;
            case self::RECURRING_ANNUAL:
                $nextDate->addYear();
                break;
        }

        // Verificar se não passou da data limite
        if ($this->recurring_until && $nextDate->gt(Carbon::parse($this->recurring_until))) {
            return null;
        }

        // Criar nova despesa
        $newExpense = $this->replicate();
        $newExpense->expense_date = $nextDate;
        $newExpense->due_date = $this->due_date ? Carbon::parse($this->due_date)->addMonths($nextDate->diffInMonths(Carbon::parse($this->expense_date))) : null;
        $newExpense->payment_date = null;
        $newExpense->status = self::STATUS_PENDING;
        $newExpense->approved_by = null;
        $newExpense->approved_at = null;
        $newExpense->invoice_number = null; // Remover número da fatura para evitar duplicação
        
        return $newExpense;
    }
}