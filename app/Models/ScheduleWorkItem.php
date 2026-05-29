<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleWorkItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'schedule_works_id',
        'technician_id',
        'start_time',
        'end_time',
        'equipment_id',
    ];

    public function equipment(){
        return $this->hasOne('App\Models\Equipment', 'id', 'equipment_id');
    }

    public function technician(){
        return $this->hasOne('App\Models\Technician', 'id', 'technician_id');
    }
}
