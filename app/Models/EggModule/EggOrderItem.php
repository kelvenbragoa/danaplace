<?php

namespace App\Models\EggModule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EggOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'inventory_id',
        'quantity',
    ];

    public function order()
    {
        return $this->belongsTo(EggOrder::class, 'order_id');
    }

    public function inventory()
    {
        return $this->belongsTo(EggInventory::class, 'inventory_id');
    }
}
