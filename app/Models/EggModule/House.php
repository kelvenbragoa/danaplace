<?php

namespace App\Models\EggModule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class House extends Model
{
    use HasFactory;
    use SoftDeletes;

    // protected $table = 'houses';
    
    protected $fillable = [
        'farm_id', 'name', 'bird_capacity', 'boxes', 'has_automation', 'code', 'is_active'
    ];

    protected $casts = [
        'has_automation' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function flocks()
    {
        return $this->hasMany(Flock::class);
    }

    public function eggInventory()
    {
        return $this->hasMany(EggInventory::class);
    }

    public function expenses()
    {
        return $this->hasMany(EggExpense::class);
    }
}
