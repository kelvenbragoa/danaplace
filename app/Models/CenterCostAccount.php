<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CenterCostAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'center_cost_id'
    ];

    public function center_cost(){
        return $this->hasOne('App\Models\CenterCost', 'id', 'center_cost_id');
    }

    public function equipments(){
        return $this->hasMany('App\Models\Equipment','center_cost_account_id','id');
    }
}
