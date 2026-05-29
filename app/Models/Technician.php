<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'department_id',
        'admission_date',
        'area_id',
        'document',
        'status',
        'salary',
        'position',
        'overtime_rate',
        'date_of_birth',
        'contact',
        'gender',
        'address',
        'province',
        'city',
        'civil_status',
        'image'
    ];

    public function department(){
        return $this->hasOne('App\Models\Department', 'id', 'department_id');
    }

    public function area(){
        return $this->hasOne('App\Models\Area', 'id', 'area_id');
    }

    public function salaryProcessItems()
    {
        return $this->hasMany(SalaryProcessItem::class);
    }

    public function absences()
    {
        return $this->hasMany(TechnicianAbsence::class);
    }

    public function vacationPlans()
    {
        return $this->hasMany(VacationPlan::class);
    }

    protected $casts = [
        'salary' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'admission_date' => 'date',
        'date_of_birth' => 'date'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
