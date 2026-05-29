<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentSubComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'criticaly_id',
        'equipment_id',
        'equipment_component_id',
        'type_equipment_sub_component_id',
        'equipment_status_id',
        'percentage_weigth',
        'ref',
        'model',
        'make',
        'serial',
    ];

    public function criticality(){
        return $this->hasOne('App\Models\Criticaly', 'id', 'criticaly_id');
    }


    public function equipmentstatus(){
        return $this->hasOne('App\Models\EquipmentStatus', 'id', 'equipment_status_id');
    }
}
