<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskMcscrDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_mcscr_id',
        'task_plan_task_department_id',
        'task_plan_task_id',
        'task_plan_id',
        'quantity',
    ];

    public function task_plan_task_department(){
        return $this->hasOne('App\Models\TaskPlanTaskDepartment', 'id', 'task_plan_task_department_id');
    }
}
