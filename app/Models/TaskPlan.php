<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskPlan extends Model
{
    use HasFactory;
    protected $fillable = [
        'name'
    ];

 

    public function equipments(){
        return $this->hasMany('App\Models\TaskPlanEquipment', 'task_plan_id', 'id');
    }

    public function taskplantasks(){
        return $this->hasMany('App\Models\TaskPlanTask', 'task_plan_id', 'id');
    }
}
