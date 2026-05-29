<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskMcscr extends Model
{
    use HasFactory;


    protected $fillable = [
        'equipment_id',
        'type_equipment_id',
        'destination_id',
        'area_id',
        'task_plan_id',
        'task_mcscr_status_id',
        'opened_by_user_id',
        'closed_by_user_id',
        'distance',
        'opened_at',
        'closed_at',
        'observation',
        'task_plan_task_id',
        'total_hours',
        'material_cost',
        'material_labor',
        'schedule_for',
        'schedule_by_user_id'
];

public function task_mcscr_status(){
    return $this->hasOne('App\Models\TaskMcscrStatus', 'id', 'task_mcscr_status_id');
}

public function equipment(){
    return $this->hasOne('App\Models\Equipment', 'id', 'equipment_id');
}

public function opened_by_user(){
    return $this->hasOne('App\Models\User', 'id', 'opened_by_user_id');
}

public function schedule_by_user(){
    return $this->hasOne('App\Models\User', 'id', 'schedule_by_user_id');
}



public function closed_by_user(){
    return $this->hasOne('App\Models\User', 'id', 'closed_by_user_id');
}

public function area(){
    return $this->hasOne('App\Models\Area', 'id', 'area_id');
}


public function destination(){
    return $this->hasOne('App\Models\Destination', 'id', 'destination_id');
}

public function task_plan(){
    return $this->hasOne('App\Models\TaskPlan', 'id', 'task_plan_id');
}

public function task_plan_task(){
    return $this->hasOne('App\Models\TaskPlanTask', 'id', 'task_plan_task_id');
}

public function subtasks(){
    return $this->hasMany('App\Models\TaskMcscrSubTask', 'task_mcscr_id', 'id');
}

public function materials(){
    return $this->hasMany('App\Models\TaskMcscrMaterial', 'task_mcscr_id', 'id');
}

public function departments(){
    return $this->hasMany('App\Models\TaskMcscrDepartment', 'task_mcscr_id', 'id');
}


public function requeststock(){
    return $this->hasMany('App\Models\RequestStock', 'task_mcscr_id', 'id');
}

}
