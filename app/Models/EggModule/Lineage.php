<?php

namespace App\Models\EggModule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lineage extends Model
{
    use HasFactory;
    // protected $table = 'lineages';
    
    protected $fillable = [
        'name', 'supplier', 'production_days', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function flocks()
    {
        return $this->hasMany(Flock::class);
    }
}
