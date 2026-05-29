<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftEquipmentRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
            'shift_id',
            'shift_equipment_request_id',
            'type_equipment_id',
            'equipment_id',
            'operator_user_id',
            'petrol',
            'moves',
            'ton',
            'distance',
            'accident',
            'warning',
            'obs',
    ];


    public function useroperator(){
        return $this->hasOne('App\Models\User', 'id', 'operator_user_id');
    }

    public function equipment(){
        return $this->hasOne('App\Models\Equipment', 'id', 'equipment_id');
    }
    
    
}
