<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $fillable = [
        'name'
    ];

    public function equipments(){
        return $this->hasMany('App\Models\Equipment','supplier_id','id');
    }

    public function available_equipments(){
        return $this->hasMany('App\Models\Equipment','supplier_id','id')->where('equipment_status_id',1);
    }

    public function unavailable_equipments(){
        return $this->hasMany('App\Models\Equipment','supplier_id','id')->where('equipment_status_id',2);
    }

    public function imobilized_equipments(){
        return $this->hasMany('App\Models\Equipment','supplier_id','id')->where('equipment_status_id',3);
    }
}
