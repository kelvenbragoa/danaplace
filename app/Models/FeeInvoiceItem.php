<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_invoice_id',
        'equipment_id',
        'fee_id',
        'amount',
        'is_paid',
        'paid_at',
        'marked_by',
        'notes',
        'payment_details'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'payment_details' => 'array'
    ];

    // Relacionamentos
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }

    public function markedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    // Acessores
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2, ',', '.') . ' MZN';
    }

    public function getStatusTextAttribute(): string
    {
        return $this->is_paid ? 'Pago' : 'Pendente';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->is_paid ? 'success' : 'warning';
    }

    public function getDaysOverdueAttribute(): int
    {
        if ($this->is_paid || !$this->invoice) return 0;
        return max(0, now()->diffInDays($this->invoice->due_date));
    }

    // Métodos de negócio
    public function markAsPaid(int $userId, array $paymentDetails = []): void
    {
        $this->is_paid = true;
        $this->paid_at = now();
        $this->marked_by = $userId;
        if (!empty($paymentDetails)) {
            $this->payment_details = $paymentDetails;
        }
        $this->save();

        // Atualizar totais da fatura
        $this->invoice->calculateTotals();
    }

    public function markAsUnpaid(): void
    {
        $this->is_paid = false;
        $this->paid_at = null;
        $this->marked_by = null;
        $this->payment_details = null;
        $this->save();

        // Atualizar totais da fatura
        $this->invoice->calculateTotals();
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    public function scopeByEquipment($query, int $equipmentId)
    {
        return $query->where('equipment_id', $equipmentId);
    }

    public function scopeByFee($query, int $feeId)
    {
        return $query->where('fee_id', $feeId);
    }
}
