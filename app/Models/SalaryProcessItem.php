<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryProcessItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_process_id',
        'technician_id',
        'base_salary',
        'overtime_hours',
        'overtime_amount',
        'bonus',
        'deductions',
        'net_salary',
        'observations',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function salaryProcess()
    {
        return $this->belongsTo(SalaryProcess::class);
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }

    // Calcula o salário líquido automaticamente
    public function calculateNetSalary()
    {
        $this->net_salary = ($this->base_salary + $this->overtime_amount + $this->bonus) - $this->deductions;
        return $this->net_salary;
    }

    // Hook para salvar automaticamente o cálculo
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->calculateNetSalary();
        });
    }
}
