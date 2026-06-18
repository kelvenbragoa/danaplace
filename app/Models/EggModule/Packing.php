<?php

namespace App\Models\EggModule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Packing extends Model
{
    use HasFactory;
    // protected $table = 'packaging';
    
    protected $fillable = [
        'classification_id', 'package_type', 'quantity_used', 
        'packaged_eggs', 'remaining_eggs', 'expiry_date', 'qr_code'
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function classification()
    {
        return $this->belongsTo(EggClassification::class);
    }
    
    public function getIsExpiredAttribute()
    {
        return $this->expiry_date->isPast();
    }
}
