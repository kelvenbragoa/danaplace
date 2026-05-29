<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
        'month',
        'description',
        'status',
        'created_by',
        'auto_generate_days'
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'auto_generate_days' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'draft',
        'auto_generate_days' => false
    ];

    /**
     * Relacionamento com turnos
     */
    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    /**
     * Relacionamento com o usuário criador
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Escopo para filtrar por status
     */
    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Escopo para filtrar por ano
     */
    public function scopeByYear($query, $year)
    {
        if ($year) {
            return $query->where('year', $year);
        }
        return $query;
    }

    /**
     * Escopo para filtrar por mês
     */
    public function scopeByMonth($query, $month)
    {
        if ($month) {
            return $query->where('month', $month);
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
                         ->orWhere('description', 'like', "%{$search}%");
        }
        return $query;
    }

    /**
     * Accessor para o nome do mês em português
     */
    public function getMonthNameAttribute()
    {
        $months = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        
        return $months[$this->month] ?? $this->month;
    }

    /**
     * Accessor para data de início do mês
     */
    public function getStartDateAttribute()
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
    }

    /**
     * Accessor para data de fim do mês
     */
    public function getEndDateAttribute()
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();
    }

    /**
     * Accessor para número de dias no mês
     */
    public function getDaysInMonthAttribute()
    {
        return $this->start_date->daysInMonth;
    }

    /**
     * Verifica se a escala está ativa
     */
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    /**
     * Verifica se a escala pode ser editada
     */
    public function getCanEditAttribute()
    {
        return in_array($this->status, ['draft', 'active']);
    }

    /**
     * Verifica se a escala pode ser copiada
     */
    public function getCanCopyAttribute()
    {
        return $this->shifts()->count() > 0;
    }

    /**
     * Obtém estatísticas da escala
     */
    public function getStats()
    {
        return [
            'total_shifts' => $this->shifts()->count(),
            'shifts_with_technicians' => $this->shifts()->whereHas('technicians')->count(),
            'total_technicians' => $this->shifts()
                ->join('shift_technician', 'shifts.id', '=', 'shift_technician.shift_id')
                ->distinct('shift_technician.user_id')
                ->count(),
            'coverage_percentage' => $this->getCoveragePercentage(),
        ];
    }

    /**
     * Calcula a porcentagem de cobertura da escala
     */
    private function getCoveragePercentage()
    {
        $totalShifts = $this->shifts()->count();
        if ($totalShifts === 0) {
            return 0;
        }
        
        $shiftsWithTechnicians = $this->shifts()->whereHas('technicians')->count();
        return round(($shiftsWithTechnicians / $totalShifts) * 100, 1);
    }

    /**
     * Copia uma escala existente
     */
    public static function copySchedule($sourceId, $newData, $copyTechnicians = false)
    {
        $source = static::with(['shifts.technicians'])->findOrFail($sourceId);
        
        // Criar nova escala
        $newSchedule = static::create([
            'name' => $newData['name'],
            'year' => $newData['year'],
            'month' => $newData['month'],
            'description' => $newData['description'] ?? $source->description,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        // Copiar turnos
        foreach ($source->shifts as $shift) {
            $newShift = $newSchedule->shifts()->create([
                'date' => Carbon::createFromDate($newData['year'], $newData['month'], $shift->created_at->day),
                'name' => $shift->name,
                'shift_type' => $shift->shift_type,
                'start_time' => $shift->start_time,
                'end_time' => $shift->end_time,
                'description' => $shift->description,
            ]);

            // Copiar técnicos se solicitado
            if ($copyTechnicians && $shift->technicians->isNotEmpty()) {
                $newShift->technicians()->attach($shift->technicians->pluck('id'));
            }
        }

        return $newSchedule;
    }

    /**
     * Gera dias automaticamente
     */
    public function generateDays()
    {
        if (!$this->auto_generate_days) {
            return;
        }

        $startDate = $this->start_date;
        $endDate = $this->end_date;

        // Tipos de turno padrão
        $shiftTypes = [
            ['name' => 'Turno da Manhã', 'type' => 'morning', 'start' => '07:00', 'end' => '15:00'],
            ['name' => 'Turno da Tarde', 'type' => 'afternoon', 'start' => '15:00', 'end' => '23:00'],
            ['name' => 'Turno da Noite', 'type' => 'night', 'start' => '23:00', 'end' => '07:00'],
        ];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Pular fins de semana se necessário (opcional)
            // if ($date->isWeekend()) continue;

            foreach ($shiftTypes as $shiftType) {
                $this->shifts()->create([
                    'date' => $date->format('Y-m-d'),
                    'name' => $shiftType['name'],
                    'shift_type' => $shiftType['type'],
                    'start_time' => $shiftType['start'],
                    'end_time' => $shiftType['end'],
                    'description' => "Turno gerado automaticamente para {$date->format('d/m/Y')}",
                ]);
            }
        }
    }

    /**
     * Boot do model
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($schedule) {
            if ($schedule->auto_generate_days) {
                $schedule->generateDays();
            }
        });
    }
}