<?php

namespace App\Models\EggModule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Farm extends Model
{
    use HasFactory;
    use SoftDeletes;

    // protected $table = 'farms';
    
    protected $fillable = [
        'name', 'tax_id', 'address', 'phone', 'email', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function houses()
    {
        return $this->hasMany(House::class);
    }
}
