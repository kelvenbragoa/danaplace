<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'extra_fields',
        'status',
    ];

    protected $casts = [
        'extra_fields' => 'array',
    ];

    public function technicians()
    {
        return $this->hasMany(Technician::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
