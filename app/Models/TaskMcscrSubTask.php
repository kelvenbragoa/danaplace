<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskMcscrSubTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_mcscr_id',
        'subtask_id',
        'answer',
        'task_plan_task_id',
        'task_plan_id',
    ];

    public function subtask(){
        return $this->hasOne('App\Models\SubTask', 'id', 'subtask_id');
    }




 

}
