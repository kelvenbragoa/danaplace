<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestTechnicianItem extends Model
{


    use HasFactory;


    protected $fillable = [

        'request_technician_id',
        'department_id',
        'technician_id',
        'obs',

    ];



    public function technician(){
        return $this->hasOne('App\Models\Technician', 'id', 'technician_id');
    }

    public function department(){
        return $this->hasOne('App\Models\Department', 'id', 'department_id');
    }
    
}
