<?php

namespace App\Models\EggModule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EggInventory extends Model
{
    use HasFactory;
    // protected $table = 'egg_inventory';
    
    protected $fillable = [
        'egg_id', 'house_id', 'quantity', 'entry_date', 'exit_date', 'location', 'status'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'exit_date' => 'date',
    ];

    public function egg()
    {
        return $this->belongsTo(Egg::class);
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }
    
    public function shipping()
    {
        return $this->hasMany(EggShipping::class, 'inventory_id');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->quantity > 0;
    }
}
