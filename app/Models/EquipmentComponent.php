<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'criticaly_id',
        'equipment_id',
        'equipment_status_id',
        'type_equipment_component_id',
        'percentage_weigth',
        'type_equipment_component_id',
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

    public function subcomponents(){
        return $this->hasMany('App\Models\EquipmentSubComponent','equipment_component_id','id');
    }
}


