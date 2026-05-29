<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EnergyReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'energy_invoice_id',
        'equipment_id', 
        'user_id',
        'reading_date',
        'reading_value',
        'previous_reading',
        'consumption',
        'notes'
    ];

    protected $casts = [
        'reading_date' => 'date',
        'reading_value' => 'decimal:2',
        'previous_reading' => 'decimal:2',
        'consumption' => 'decimal:2'
    ];

    protected $appends = [
        'reading_date_formatted',
        'consumption_formatted'
    ];

    /**
     * Relacionamento com EnergyInvoice
     */
    public function energyInvoice()
    {
        return $this->belongsTo(EnergyInvoice::class);
    }

    /**
     * Relacionamento com Equipment
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Relacionamento com User (técnico)
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Accessor para data formatada
     */
    public function getReadingDateFormattedAttribute()
    {
        return $this->reading_date ? $this->reading_date->format('d/m/Y') : null;
    }

    /**
     * Accessor para consumo formatado
     */
    public function getConsumptionFormattedAttribute()
    {
        return number_format($this->consumption, 2, ',', '.');
    }

    /**
     * Calcular o consumo automaticamente
     */
    public function calculateConsumption()
    {
        if ($this->reading_value && $this->previous_reading) {
            $this->consumption = $this->reading_value - $this->previous_reading;
        }
        return $this;
    }

    /**
     * Scope para filtrar por fatura
     */
    public function scopeByInvoice($query, $invoiceId)
    {
        return $query->where('energy_invoice_id', $invoiceId);
    }

    /**
     * Scope para filtrar por equipamento
     */
    public function scopeByEquipment($query, $equipmentId)
    {
        return $query->where('equipment_id', $equipmentId);
    }

    /**
     * Scope para filtrar por período
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('reading_date', [$startDate, $endDate]);
    }

    /**
     * Scope para filtrar por técnico
     */
    public function scopeByTechnician($query, $technicianId)
    {
        return $query->where('user_id', $technicianId);
    }

    /**
     * Obter leitura anterior para um equipamento específico
     */
    public static function getPreviousReading($equipmentId, $date)
    {
        return static::where('equipment_id', $equipmentId)
            ->where('reading_date', '<', $date)
            ->orderBy('reading_date', 'desc')
            ->first();
    }

    /**
     * Boot method para calcular consumo automaticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($reading) {
            // Calcular consumo automaticamente se não foi definido
            if (!$reading->consumption && $reading->reading_value && $reading->previous_reading) {
                $reading->consumption = $reading->reading_value - $reading->previous_reading;
            }

            // Se não há leitura anterior definida, buscar automaticamente
            if (!$reading->previous_reading && $reading->equipment_id && $reading->reading_date) {
                $previousReading = static::getPreviousReading($reading->equipment_id, $reading->reading_date);
                if ($previousReading) {
                    $reading->previous_reading = $previousReading->reading_value;
                    $reading->consumption = $reading->reading_value - $reading->previous_reading;
                }
            }
        });
    }
}