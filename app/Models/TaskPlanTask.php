<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskPlanTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'task_plan_id',
        'type_task_id',
        'critical_id',
        'estimated_time_days',
        'estimated_time_hours',
        'estimated_time_minutes',
        'unavailable_equipment_time_days',
        'unavailable_equipment_time_hours',
        'unavailable_equipment_time_minutes',
        'do_every',
        'frequency_id'
    ];


    public function frequency(){
        return $this->hasOne('App\Models\Frequency', 'id', 'frequency_id');
    }

    public function critical(){
        return $this->hasOne('App\Models\Criticaly', 'id', 'critical_id');
    }

    public function typetask(){
        return $this->hasOne('App\Models\Task', 'id', 'type_task_id');
    }

    public function subtasks(){
        return $this->hasMany('App\Models\SubTask', 'task_plan_task_id', 'id');
    }
}
