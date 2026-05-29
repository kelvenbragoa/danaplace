<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupShiftOperators extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_id',
        'group_shift_id',
    ];

    public function shift(){
        return $this->hasOne('App\Models\Shift', 'id', 'shift_id');
    }

    public function groupshift(){
        return $this->hasOne('App\Models\GroupShift', 'id', 'group_shift_id');
    }

    public function user(){
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
