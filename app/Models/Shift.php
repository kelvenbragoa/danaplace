<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_schedule_id',
        'date',
        'name',
        'shift_type',
        'start_time',
        'end_time',
        'description',
        'status'
    ];

    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'scheduled'
    ];

    /**
     * Relacionamento com escala de trabalho
     */
    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    /**
     * Relacionamento com técnicos (many-to-many)
     */
    public function technicians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shift_technician', 'shift_id', 'user_id')
                    ->withTimestamps();
    }

    // Legacy relationship - manter compatibilidade
    public function user(){
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    /**
     * Escopo para filtrar por escala
     */
    public function scopeBySchedule($query, $scheduleId)
    {
        if ($scheduleId) {
            return $query->where('work_schedule_id', $scheduleId);
        }
        return $query;
    }

    /**
     * Escopo para filtrar por tipo de turno
     */
    public function scopeByShiftType($query, $shiftType)
    {
        if ($shiftType) {
            return $query->where('shift_type', $shiftType);
        }
        return $query;
    }

    /**
     * Escopo para filtrar por data
     */
    public function scopeByDate($query, $date)
    {
        if ($date) {
            return $query->whereDate('date', $date);
        }
        return $query;
    }

    /**
     * Escopo para buscar por nome
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('name', 'like', "%{$search}%")
                         ->orWhere('description', 'like', "%{$search}%")
                         ->orWhereHas('technicians', function ($q) use ($search) {
                             $q->where('name', 'like', "%{$search}%");
                         });
        }
        return $query;
    }

    /**
     * Escopo para turnos de hoje
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', now()->format('Y-m-d'));
    }

    /**
     * Escopo para turnos ativos (em andamento)
     */
    public function scopeActive($query)
    {
        $now = now();
        return $query->whereDate('date', $now->format('Y-m-d'))
                     ->whereTime('start_time', '<=', $now->format('H:i:s'))
                     ->whereTime('end_time', '>=', $now->format('H:i:s'));
    }

    /**
     * Escopo para turnos futuros
     */
    public function scopeUpcoming($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->whereDate('date', '>', $now->format('Y-m-d'))
              ->orWhere(function ($subQ) use ($now) {
                  $subQ->whereDate('date', $now->format('Y-m-d'))
                       ->whereTime('start_time', '>', $now->format('H:i:s'));
              });
        });
    }

    /**
     * Accessor para status calculado
     */
    public function getCalculatedStatusAttribute()
    {
        $now = now();
        $shiftDate = $this->date;
        
        if (!$shiftDate->isToday()) {
            return $shiftDate->isPast() ? 'completed' : 'scheduled';
        }
        
        $shiftStart = Carbon::parse("{$this->date->format('Y-m-d')} {$this->start_time}");
        $shiftEnd = Carbon::parse("{$this->date->format('Y-m-d')} {$this->end_time}");
        
        // Handle overnight shifts
        if ($shiftEnd->lt($shiftStart)) {
            $shiftEnd->addDay();
        }
        
        if ($now->between($shiftStart, $shiftEnd)) {
            return 'active';
        } elseif ($now->gt($shiftEnd)) {
            return 'completed';
        } else {
            return 'scheduled';
        }
    }

    /**
     * Accessor para rótulo do status
     */
    public function getStatusLabelAttribute()
    {
        $status = $this->calculated_status;
        
        $labels = [
            'active' => 'Em Andamento',
            'scheduled' => 'Programado',
            'completed' => 'Finalizado'
        ];
        
        return $labels[$status] ?? 'Desconhecido';
    }

    /**
     * Accessor para ícone do tipo de turno
     */
    public function getShiftTypeIconAttribute()
    {
        $icons = [
            'morning' => '🌅',
            'afternoon' => '☀️',
            'evening' => '🌆',
            'night' => '🌙'
        ];
        
        return $icons[$this->shift_type] ?? '🕒';
    }

    /**
     * Accessor para rótulo do tipo de turno
     */
    public function getShiftTypeLabelAttribute()
    {
        $labels = [
            'morning' => 'Manhã',
            'afternoon' => 'Tarde',
            'evening' => 'Noite',
            'night' => 'Madrugada'
        ];
        
        return $labels[$this->shift_type] ?? 'Geral';
    }

    /**
     * Calcula a duração do turno
     */
    public function getDurationAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return '';
        }
        
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        
        // Handle overnight shifts
        if ($end->lt($start)) {
            $end->addDay();
        }
        
        $duration = $start->diff($end);
        
        $hours = $duration->h;
        $minutes = $duration->i;
        
        if ($minutes === 0) {
            return "{$hours}h";
        } else {
            return "{$hours}h {$minutes}min";
        }
    }

    /**
     * Verifica se o turno pode ser editado
     */
    public function getCanEditAttribute()
    {
        // Can edit future shifts or shifts from today
        return $this->date->isToday() || $this->date->isFuture();
    }

    /**
     * Verifica se o turno pode ser excluído
     */
    public function getCanDeleteAttribute()
    {
        // Can only delete future shifts
        return $this->date->isFuture();
    }

    /**
     * Verifica se o status pode ser alterado
     */
    public function getCanToggleStatusAttribute()
    {
        // Can toggle status for today's shifts only
        return $this->date->isToday();
    }

    /**
     * Verifica se o turno está ativo agora
     */
    public function getIsActiveNowAttribute()
    {
        return $this->calculated_status === 'active';
    }

    /**
     * Obtém técnicos com informações adicionais
     */
    public function getTechniciansWithDetailsAttribute()
    {
        return $this->technicians()->select([
            'users.id',
            'users.name',
            'users.email',
            'users.phone',
            'departments.name as department_name'
        ])
        ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
        ->get();
    }

    /**
     * Copia um turno para uma nova data
     */
    public function copyToDate($newDate, $copyTechnicians = false)
    {
        $newShift = static::create([
            'work_schedule_id' => $this->work_schedule_id,
            'date' => $newDate,
            'name' => $this->name,
            'shift_type' => $this->shift_type,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'description' => $this->description,
        ]);

        if ($copyTechnicians && $this->technicians->isNotEmpty()) {
            $newShift->technicians()->attach($this->technicians->pluck('id'));
        }

        return $newShift;
    }

    /**
     * Alterna o status do turno (apenas para turnos de hoje)
     */
    public function toggleStatus()
    {
        if (!$this->can_toggle_status) {
            return false;
        }

        $currentStatus = $this->calculated_status;
        
        if ($currentStatus === 'scheduled') {
            $this->status = 'active';
        } elseif ($currentStatus === 'active') {
            $this->status = 'completed';
        }
        
        return $this->save();
    }

    /**
     * Obtém estatísticas do turno
     */
    public static function getStats($filters = [])
    {
        $query = static::query();
        
        // Apply filters
        if (isset($filters['schedule_id'])) {
            $query->bySchedule($filters['schedule_id']);
        }
        if (isset($filters['shift_type'])) {
            $query->byShiftType($filters['shift_type']);
        }
        if (isset($filters['date'])) {
            $query->byDate($filters['date']);
        }
        
        return [
            'total_shifts' => $query->count(),
            'shifts_today' => static::today()->count(),
            'active_shifts' => static::active()->count(),
            'technicians_assigned' => static::join('shift_technician', 'shifts.id', '=', 'shift_technician.shift_id')
                                           ->distinct('shift_technician.user_id')
                                           ->count(),
        ];
    }
}
