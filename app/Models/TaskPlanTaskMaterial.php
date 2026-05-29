<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskPlanTaskMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_name',
        'material',
        'task_plan_task_id',
        'quantity'
    ];


    public function product(){

        return $this->hasOne('App\Models\Product', 'id', 'product_id');
        
    }
}
