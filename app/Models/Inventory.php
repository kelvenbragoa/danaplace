<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_center_id',
        'ref',
        'products_number',
        'user_id',
        
    ];

    public function stockcenter(){
        return $this->hasOne('App\Models\StockCenter', 'id', 'stock_center_id');
    }

    public function itens(){
        return $this->hasMany('App\Models\InventoryItem','inventory_id','id');
    }


}
