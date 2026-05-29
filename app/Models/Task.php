<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TaskPlanTask;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
    ];

    public function mcscr(){
        return $this->hasMany('App\Models\Mcscr','task_id','id');
    }

    public function tasksmcscr(){

    }


}
