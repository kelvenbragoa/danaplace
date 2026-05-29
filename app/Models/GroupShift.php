<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'shift_id',
        
    ];

    public function shift(){
        return $this->hasOne('App\Models\Shift', 'id', 'shift_id');
    }

    public function groupshiftoperators(){

        return $this->hasMany('App\Models\GroupShiftOperators','group_shift_id','id');
        
    }
}
