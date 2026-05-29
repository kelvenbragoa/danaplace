<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class FeeInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'month',
        'year',
        'issue_date',
        'due_date',
        'notes',
        'total_amount',
        'paid_amount',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'metadata'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'metadata' => 'array'
    ];

    // Relacionamentos
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FeeInvoiceItem::class);
    }

    public function paidItems(): HasMany
    {
        return $this->hasMany(FeeInvoiceItem::class)->where('is_paid', true);
    }

    public function unpaidItems(): HasMany
    {
        return $this->hasMany(FeeInvoiceItem::class)->where('is_paid', false);
    }

    // Acessores e Mutators
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->total_amount, 2, ',', '.') . ' MZN';
    }

    public function getFormattedPaidAmountAttribute(): string
    {
        return number_format($this->paid_amount, 2, ',', '.') . ' MZN';
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getFormattedRemainingAmountAttribute(): string
    {
        return number_format($this->remaining_amount, 2, ',', '.') . ' MZN';
    }

    public function getPaymentPercentageAttribute(): float
    {
        if ($this->total_amount == 0) return 0;
        return ($this->paid_amount / $this->total_amount) * 100;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date < now() && $this->status !== 'paid';
    }

    public function getDaysUntilDueAttribute(): int
    {
        return now()->diffInDays($this->due_date, false);
    }

    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        return $months[$this->month] ?? '';
    }

    public function getPeriodDescriptionAttribute(): string
    {
        return $this->month_name . '/' . $this->year;
    }

    // Métodos de negócio
    public function calculateTotals(): void
    {
        $this->total_amount = $this->items()->sum('amount');
        $this->paid_amount = $this->items()->where('is_paid', true)->sum('amount');
        $this->updateStatus();
        $this->save();
    }

    public function updateStatus(): void
    {
        if ($this->paid_amount == 0) {
            $this->status = $this->is_overdue ? 'overdue' : 'issued';
        } elseif ($this->paid_amount >= $this->total_amount) {
            $this->status = 'paid';
        } else {
            $this->status = 'partially_paid';
        }
    }

    public function markAsApproved(int $userId): void
    {
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->status = 'issued';
        $this->save();
    }

    public function generateInvoiceNumber(): string
    {
        $prefix = 'FT';
        $yearMonth = $this->year . str_pad($this->month, 2, '0', STR_PAD_LEFT);
        $sequence = static::where('month', $this->month)
            ->where('year', $this->year)
            ->count() + 1;
        
        return $prefix . $yearMonth . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // Scopes
    public function scopeByPeriod($query, int $month, int $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['paid', 'cancelled']);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
