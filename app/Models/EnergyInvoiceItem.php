<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyInvoiceItem extends Model
{
    use HasFactory;
    use HasFactory;
    protected $guarded = [];

    public function equipment(){
        return $this->hasOne('App\Models\Equipment', 'id', 'equipment_id');
    }

    public function destination(){
        return $this->hasOne('App\Models\Destination', 'id', 'destination_id');
    }

    public function energyinvoice(){
        return $this->hasOne('App\Models\EnergyInvoice', 'id', 'energy_invoice_id');
    }

    public function markedByUser(){
        return $this->belongsTo(User::class, 'marked_by', 'id');
    }

    protected $fillable = [
        'energy_invoice_id', 'equipment_id', 'destination_id', 'apr_consumption',
        'meter', 'cost', 'percentage_value', 'tax_iva', 'total', 'total_to_invoice',
        'is_paid', 'paid_at', 'marked_by', 'payment_details'
    ];

    protected $casts = [
        'apr_consumption'       => 'decimal:1',
        'meter'  => 'decimal:1',
        'cost'     => 'decimal:1',
        'percentage_value'=> 'decimal:1',
        'tax_iva'                            => 'decimal:1',
        'total'                       => 'decimal:1',
        'total_to_invoice'                           => 'decimal:1',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'payment_details' => 'array'
    ];
}
