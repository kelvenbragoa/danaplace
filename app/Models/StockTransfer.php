<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref',
        'stock_center_origin_id',
        'stock_center_destination_id',

    ];

    public function stockcenterorigin(){
        return $this->hasOne('App\Models\StockCenter', 'id', 'stock_center_origin_id');
    }

    public function stockcenterdestination(){
        return $this->hasOne('App\Models\StockCenter', 'id', 'stock_center_destination_id');
    }


    public function itens(){
        return $this->hasMany('App\Models\StockTransferItem','stock_transfer_id','id');
    }

    
    
}
