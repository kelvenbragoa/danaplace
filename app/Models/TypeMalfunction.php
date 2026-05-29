<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeMalfunction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function mcscr(){
        return $this->hasMany('App\Models\Mcscr','type_malfunction_id','id');
    }

    public function mcscr_done(){
        return $this->hasMany('App\Models\Mcscr','type_malfunction_id','id')->where('mcscr_status_id',1);
    }
    public function mcscr_not_done(){
        return $this->hasMany('App\Models\Mcscr','type_malfunction_id','id')->where('mcscr_status_id',3);
    }
    public function mcscr_approval(){
        return $this->hasMany('App\Models\Mcscr','type_malfunction_id','id')->where('mcscr_status_id',2);
    }
}
