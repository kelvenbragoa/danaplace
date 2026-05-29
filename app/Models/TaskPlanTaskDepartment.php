<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskPlanTaskDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'department_name',
        'task_plan_task_id',
        'quantity'
    ];

    public function department(){

        return $this->hasOne('App\Models\Department', 'id', 'department_id');
        
    }

}
