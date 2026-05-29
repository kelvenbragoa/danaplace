<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingTask extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function participant(){
        return $this->hasOne('App\Models\MeetingParticipant', 'id', 'meeting_participant_id');
    }

    public function meeting(){
        return $this->hasOne('App\Models\Meeting', 'id', 'meeting_id');
    }

    public function status(){
        return $this->hasOne('App\Models\MeetingTaskStatus', 'id', 'status_id');
    }

}
