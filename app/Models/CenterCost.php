<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CenterCost extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code'
    ];

    public function accounts(){
        return $this->hasMany('App\Models\CenterCostAccount', 'center_cost_id', 'id');
    }
    public function equipments(){
        return $this->hasMany('App\Models\Equipment','center_cost_id','id');
    }
}
