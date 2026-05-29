<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'company_name',
        'company_address',
        'company_nuit',
        'province_id',
        'company_mobile',
        'company_email',
    ];

    public function province(){
        return $this->hasOne('App\Models\Province', 'id', 'province_id');
    }

    public function equipments(){
        return $this->hasMany('App\Models\Equipment','area_id','id');
    }

    public function available_equipments(){
        return $this->hasMany('App\Models\Equipment','area_id','id')->where('equipment_status_id',1);
    }

    public function unavailable_equipments(){
        return $this->hasMany('App\Models\Equipment','area_id','id')->where('equipment_status_id',2);
    }

    public function imobilized_equipments(){
        return $this->hasMany('App\Models\Equipment','area_id','id')->where('equipment_status_id',3);
    }

    public function task_mcscr(){
        return $this->hasMany('App\Models\TaskMcscr','area_id','id');
    }

    public function mcscr(){
        return $this->hasMany('App\Models\Mcscr','area_id','id');
    }

}
