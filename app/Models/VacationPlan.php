<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class VacationPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'technician_id',
        'year',
        'start_date',
        'end_date',
        'days_requested',
        'days_approved',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'notes',
        'replacement_technician_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'days_requested' => 'integer',
        'days_approved' => 'integer'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXECUTED = 'executed';

    /**
     * Relacionamento com o técnico
     */
    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }

    /**
     * Relacionamento com o técnico substituto
     */
    public function replacementTechnician()
    {
        return $this->belongsTo(Technician::class, 'replacement_technician_id');
    }

    /**
     * Relacionamento com quem solicitou
     */
    public function requestedByUser()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Relacionamento com quem aprovou
     */
    public function approvedByUser()
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
     * Scope para filtrar por ano
     */
    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope para filtrar por técnico
     */
    public function scopeByTechnician($query, $technicianId)
    {
        return $query->where('technician_id', $technicianId);
    }

    /**
     * Calcular dias úteis entre duas datas
     */
    public function calculateWorkingDays()
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        
        $workingDays = 0;
        
        while ($startDate->lte($endDate)) {
            // Contar apenas dias úteis (segunda a sexta)
            if ($startDate->isWeekday()) {
                $workingDays++;
            }
            $startDate->addDay();
        }
        
        return $workingDays;
    }

    /**
     * Verificar se as férias estão no futuro
     */
    public function isFuture()
    {
        return $this->start_date && Carbon::parse($this->start_date)->isFuture();
    }

    /**
     * Verificar se as férias estão em andamento
     */
    public function isActive()
    {
        if (!$this->start_date || !$this->end_date) {
            return false;
        }

        $now = Carbon::now();
        return $now->between(
            Carbon::parse($this->start_date),
            Carbon::parse($this->end_date)
        );
    }

    /**
     * Verificar se as férias já terminaram
     */
    public function isPast()
    {
        return $this->end_date && Carbon::parse($this->end_date)->isPast();
    }

    /**
     * Obter status formatado
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_APPROVED => 'Aprovado',
            self::STATUS_REJECTED => 'Rejeitado',
            self::STATUS_EXECUTED => 'Executado'
        ];

        return $labels[$this->status] ?? 'Desconhecido';
    }

    /**
     * Obter cor do status para interface
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_EXECUTED => 'info'
        ];

        return $colors[$this->status] ?? 'secondary';
    }
}