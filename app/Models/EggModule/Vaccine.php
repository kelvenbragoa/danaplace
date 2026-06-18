<?php

namespace App\Models\EggModule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaccine extends Model
{
    use HasFactory;
    // protected $table = 'vaccines';
    
    protected $fillable = [
        'name', 'manufacturer', 'batch', 'expiry_date', 'min_stock'
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function vaccinationSchedule()
    {
        return $this->hasMany(VaccineSchedule::class);
    }

    public function getIsExpiredAttribute()
    {
        return $this->expiry_date->isPast();
    }
}
