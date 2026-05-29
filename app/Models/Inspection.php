<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function equipment(){
        return $this->hasOne('App\Models\Equipment', 'id', 'equipment_id');
    }


    public function inspection_status(){
        return $this->hasOne('App\Models\InspectioStatus', 'id', 'inspection_status_id');
    }

    public function area(){
        return $this->hasOne('App\Models\Area', 'id', 'area_id');
    }

    public function destination(){
        return $this->hasOne('App\Models\Destination', 'id', 'destination_id');
    }

    public function opened_by_user(){
        return $this->hasOne('App\Models\User', 'id', 'opened_by_user_id');
    }

}
