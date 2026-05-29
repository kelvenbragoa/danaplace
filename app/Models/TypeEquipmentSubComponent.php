<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeEquipmentSubComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'criticaly_id',
        'type_equipment_id',
        'type_equipment_component_id',
        'percentage_weigth',
        'model',
        'make',
    ];

    public function criticality(){
        return $this->hasOne('App\Models\Criticaly', 'id', 'criticaly_id');
    }
}
