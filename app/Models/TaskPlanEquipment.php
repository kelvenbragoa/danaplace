<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskPlanEquipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_plan_id',
        'equipment_id',
        'type_equipment_id'
    ];

    public function taskplan(){
        return $this->hasOne('App\Models\TaskPlan', 'id', 'task_plan_id');
    }

    public function equipment(){
        return $this->hasOne('App\Models\Equipment', 'id', 'equipment_id');
    }
}
