<?php

namespace App\Models\EggModule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EggCategory extends Model
{
    use HasFactory;
    // protected $table = 'egg_categories';
    
    protected $fillable = [
        'name', 'min_weight', 'max_weight', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function eggs()
    {
        return $this->hasMany(Egg::class, 'category_id');
    }

    public function orders()
    {
        return $this->hasMany(EggOrder::class, 'category_id');
    }
}
