<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftEquipmentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
            'type_equipment_id',
            'request_quantity',
            'delivered_quantity',
            'status',
            'created_by_user_id',
            'answered_by_user_id',
            'obs',
            'answered_date'
    ];


    public function typeequipment(){
        return $this->hasOne('App\Models\TypeEquipment', 'id', 'type_equipment_id');
    }

    public function createdbyuser(){
        return $this->hasOne('App\Models\User', 'id', 'created_by_user_id');
    }

    public function answeredbyuser(){
        return $this->hasOne('App\Models\User', 'id', 'answered_by_user_id');
    }

    public function shift(){
        return $this->hasOne('App\Models\Shift', 'id', 'shift_id');
    }

   
}
