<?php

namespace App\Models\EggModule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EggShippingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'egg_shipping_id',
        'inventory_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function shipping()
    {
        return $this->belongsTo(EggShipping::class, 'egg_shipping_id');
    }

    public function inventory()
    {
        return $this->belongsTo(EggInventory::class, 'inventory_id');
    }
}
