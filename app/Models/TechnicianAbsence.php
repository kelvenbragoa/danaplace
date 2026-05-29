<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TechnicianAbsence extends Model
{
    use HasFactory;

    protected $fillable = [
        'technician_id',
        'date',
        'type',
        'hours_lost',
        'reason',
        'status',
        'observations',
        'created_by_user_id',
        'approved_by_user_id',
        'approved_at'
    ];

    protected $casts = [
        'date' => 'date',
        'hours_lost' => 'decimal:2',
        'approved_at' => 'timestamp'
    ];

    // Relationships
    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    // Scopes
    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForTechnician(Builder $query, $technicianId)
    {
        return $query->where('technician_id', $technicianId);
    }

    public function scopeForPeriod(Builder $query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeForMonth(Builder $query, $month, $year)
    {
        return $query->whereMonth('date', $month)
                    ->whereYear('date', $year);
    }

    // Métodos auxiliares
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'absence' => 'Falta',
            'late_arrival' => 'Atraso',
            'early_departure' => 'Saída Antecipada',
            default => $this->type
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            default => $this->status
        };
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-warning',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    // Métodos de cálculo
    public static function calculateTotalHoursLostForTechnician($technicianId, $startDate, $endDate)
    {
        return self::approved()
                  ->forTechnician($technicianId)
                  ->forPeriod($startDate, $endDate)
                  ->sum('hours_lost');
    }

    public static function calculateDeductionForTechnician($technicianId, $startDate, $endDate, $hourlyRate)
    {
        $totalHoursLost = self::calculateTotalHoursLostForTechnician($technicianId, $startDate, $endDate);
        return $totalHoursLost * $hourlyRate;
    }

    public static function getAbsencesForSalaryProcess($technicianId, $month, $year)
    {
        return self::approved()
                  ->forTechnician($technicianId)
                  ->forMonth($month, $year)
                  ->get();
    }

    // Método para aprovar/rejeitar
    public function approve($userId, $observations = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_by_user_id' => $userId,
            'approved_at' => now(),
            'observations' => $observations
        ]);
    }

    public function reject($userId, $observations = null)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by_user_id' => $userId,
            'approved_at' => now(),
            'observations' => $observations
        ]);
    }

    // Validações de negócio
    public function canBeEdited()
    {
        return $this->status === 'pending';
    }

    public function canBeDeleted()
    {
        return $this->status === 'pending';
    }
}
