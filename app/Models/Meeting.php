<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function typemeeting(){
        return $this->hasOne('App\Models\TypeOfMeeting', 'id', 'type_meeting_id');
    }

    public function participants(){
        return $this->hasMany('App\Models\MeetingParticipant','meeting_id','id');
    }

    public function completed_tasks(){
        return $this->hasMany('App\Models\MeetingTask','meeting_id','id')->where('status_id',1);
    }

    public function incompleted_tasks(){
        return $this->hasMany('App\Models\MeetingTask','meeting_id','id')->where('status_id',2);
    }

    public function waiting_approval_tasks(){
        return $this->hasMany('App\Models\MeetingTask','meeting_id','id')->where('status_id',4);
    }

    public function completed_out_of_time_tasks(){
        return $this->hasMany('App\Models\MeetingTask','meeting_id','id')->where('status_id',3);
    }



}
