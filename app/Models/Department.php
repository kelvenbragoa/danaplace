<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];
    public function technicians(){
        return $this->hasMany('App\Models\Technician','department_id','id');
    }

    public function technicians_available(){

        return $this->hasMany('App\Models\Technician','department_id','id')->where('status',1);
        
    }

    
}
