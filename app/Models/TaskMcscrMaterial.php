<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskMcscrMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_mcscr_id',
        'task_plan_task_material_id',
        'task_plan_task_id',
        'task_plan_id',
        'quantity',
    ];

    public function task_plan_task_material(){
        return $this->hasOne('App\Models\TaskPlanTaskMaterial', 'id', 'task_plan_task_material_id');
    }
}
