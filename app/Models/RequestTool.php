<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestTool extends Model
{
    use HasFactory;

    protected $fillable = [

        'task_mcscr_id',
        'mcscr_id',
        'first_observation',
        'final_observation',
        'created_by_user_id',
        'approved_by_user_id',
        'approved_date',
        'delivered_date',
        'delivered_by_user_id',
        'request_tool_status_id',
        
    ];

    public function createdbyuser(){
        return $this->hasOne('App\Models\User','id','created_by_user_id');
    }

    public function approvedbyuser(){
        return $this->hasOne('App\Models\User','id','approved_by_user_id');
    }

    public function deliveredbyuser(){
        return $this->hasOne('App\Models\User','id','delivered_by_user_id');
    }

    public function status(){
        return $this->hasOne('App\Models\RequestToolStatus','id','request_tool_status_id');
    }

    public function mcscr(){
        return $this->hasOne('App\Models\Mcscr','id','mcscr_id');
    }

    public function taskmcscr(){
        return $this->hasOne('App\Models\TaskMcscr','id','task_mcscr_id');
    }
    
    public function requestitens(){
        return $this->hasMany('App\Models\RequestToolItem', 'request_tool_id', 'id');
    }
}
