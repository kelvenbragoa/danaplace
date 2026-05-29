<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestStockItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'request_stock_id',
        'product_id',
        'stock_center_id',
        'quantity',
        'delivered_quantity',
        'obs',
    ];


    public function product(){
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }
}
