<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'product_brand_id',
        'product_category_id',
        'quantity',
        'stock_min',
        'unity_price',
        'tax_iva_id',
        'unit_id',
        'unity_buy_price'
    ];


    public function brand(){
        return $this->hasOne('App\Models\ProductBrand','id','product_brand_id');
    }

    public function category(){
        return $this->hasOne('App\Models\ProductCategory','id','product_category_id');
    }

    public function iva(){
        return $this->hasOne('App\Models\TaxIva','id','tax_iva_id');
    }


    public function unity(){
        return $this->hasOne('App\Models\Unit','id','unit_id');
    }


}
