<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockCenter extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'code'
    ];

    public function stockcenterproducts(){
        return $this->hasMany('App\Models\StockCenterProduct','stock_center_id','id');
    }
}
