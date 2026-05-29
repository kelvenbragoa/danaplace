<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTask extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'type_sub_task_id',
        'task_plan_task_id'
    ];


    

    public function typesubtask(){
        return $this->hasOne('App\Models\TypeSubTask', 'id', 'type_sub_task_id');
    }
}
