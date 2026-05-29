<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
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
        'image',
        'is_logistic',
        'user_id'
    ];

    public function province(){
        return $this->hasOne('App\Models\Province', 'id', 'province_id');
    }

    public function equipments(){
        return $this->hasMany('App\Models\Equipment','destination_id','id');
    }

    public function available_equipments(){
        return $this->hasMany('App\Models\Equipment','destination_id','id')->where('equipment_status_id',1);
    }

    public function unavailable_equipments(){
        return $this->hasMany('App\Models\Equipment','destination_id','id')->where('equipment_status_id',2);
    }

    public function imobilized_equipments(){
        return $this->hasMany('App\Models\Equipment','destination_id','id')->where('equipment_status_id',3);
    }
    public function task_mcscr(){
        return $this->hasMany('App\Models\TaskMcscr','area_id','id');
    }

    public function mcscr(){
        return $this->hasMany('App\Models\Mcscr','area_id','id');
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function entryGuides(){
        return $this->hasMany('App\Models\EntryGuide','destination_id','id');
    }

    public function activeEntryGuides(){
        return $this->hasMany('App\Models\EntryGuide','destination_id','id')->where('status', 'active');
    }
}
