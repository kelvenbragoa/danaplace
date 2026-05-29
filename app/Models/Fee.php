<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'integer'
    ];

    // Relacionamentos
    public function equipments()
    {
        return $this->belongsToMany(Equipment::class, 'equipment_fees', 'fee_id', 'equipment_id');
    }

    public function equipmentFees()
    {
        return $this->hasMany(EquipmentFee::class);
    }

    public function feeInvoiceItems()
    {
        return $this->hasMany(FeeInvoiceItem::class);
    }

    // Acessores
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2, ',', '.') . ' MZN';
    }

    public function getStatusTextAttribute(): string
    {
        return $this->status == 1 ? 'Ativa' : 'Inativa';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status == 1 ? 'success' : 'secondary';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }
}
