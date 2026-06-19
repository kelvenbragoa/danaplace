<?php

namespace App\Models\EggModule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EggOrder extends Model
{
    use HasFactory;
    // protected $table = 'egg_orders';
    
    protected $fillable = [
        'customer_id',
        'customer_name', 'customer_tax_id', 'customer_email', 'customer_phone',
        'order_date', 'expected_delivery_date', 'category_id', 'quantity_dozens',
        'unit_price', 'status', 'observations'
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(EggCustomer::class, 'customer_id');
    }

    public function category()
    {
        return $this->belongsTo(EggCategory::class, 'category_id');
    }
    
    public function shipping()
    {
        return $this->hasOne(EggShipping::class, 'order_id');
    }
    
    public function getTotalValueAttribute()
    {
        return $this->quantity_dozens * $this->unit_price;
    }
}
