<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyInvoice extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    protected $fillable = [
        'start_date_period', 'end_date_period', 'active_energy_consumption', 
        'active_energy_consumption_cost', 'reactive_energy_consumption', 
        'reactive_energy_consumption_cost', 'loss', 'loss_cost', 'ponta', 
        'ponta_cost', 'fix_rate', 'fix_rate_cost', 'tax_iva', 
        'invoice_total_cost', 'status'
    ];

    // Campos que são calculados dinamicamente mas não salvos no banco
    protected $appends = [];
    
    // Campos que não devem ser salvos no banco
    protected $hidden = [];
    
    // Impedir que campos dinâmicos sejam salvos
    public function getDirty()
    {
        $dirty = parent::getDirty();
        
        // Remover campos dinâmicos que não existem na tabela
        unset(
            $dirty['paid_items_count'], 
            $dirty['unpaid_items_count'], 
            $dirty['paid_amount'], 
            $dirty['remaining_amount'], 
            $dirty['total_amount']
        );
        
        return $dirty;
    }

    public function energy_invoice_items(){
        return $this->hasMany('App\Models\EnergyInvoiceItem','energy_invoice_id','id');
    }

    public function energyInvoiceItems(){
        return $this->hasMany(EnergyInvoiceItem::class, 'energy_invoice_id', 'id');
    }

    public function paidItems(){
        return $this->hasMany(EnergyInvoiceItem::class, 'energy_invoice_id', 'id')
                   ->where('is_paid', true);
    }

    public function unpaidItems(){
        return $this->hasMany(EnergyInvoiceItem::class, 'energy_invoice_id', 'id')
                   ->where('is_paid', false);
    }

    /**
     * Relacionamento com EnergyReading (leituras diárias)
     */
    public function energyReadings()
    {
        return $this->hasMany(EnergyReading::class, 'energy_invoice_id', 'id');
    }

    protected $casts = [
        'active_energy_consumption'       => 'decimal:1',
        'active_energy_consumption_cost'  => 'decimal:1',
        'reactive_energy_consumption'     => 'decimal:1',
        'reactive_energy_consumption_cost'=> 'decimal:1',
        'loss'                            => 'decimal:1',
        'loss_cost'                       => 'decimal:1',
        'ponta'                           => 'decimal:1',
        'ponta_cost'                      => 'decimal:1',
        'fix_rate'                        => 'decimal:1',
        'fix_rate_cost'                   => 'decimal:1',
        'tax_iva'                         => 'decimal:1',
        'invoice_total_cost'              => 'decimal:1',
        'total_to_invoice_items'          => 'decimal:1',
        'total_value_items'               => 'decimal:1',
        'total_cost_items'                => 'decimal:1',
        'total_apr_consumption'           => 'decimal:1',
    ];
}
