<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaterConsumption extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function area(){
        return $this->hasOne('App\Models\Area', 'id', 'area_id');
    }

    public function type_equipment(){
        return $this->hasOne('App\Models\TypeEquipment', 'id', 'type_equipment_id');
    }

    public function destination(){
        return $this->hasOne('App\Models\Destination', 'id', 'destination_id');
    }

    public function distance_control(){
        return $this->hasOne('App\Models\DistanceControl', 'id', 'distance_control_id');
    }

    public function equipment(){
        return $this->hasOne('App\Models\Equipment', 'id', 'equipment_id');
    }

}
