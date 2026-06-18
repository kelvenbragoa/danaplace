<?php

namespace App\Models\EggModule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Egg extends Model
{
    use HasFactory;
    // protected $table = 'eggs';
    
    protected $fillable = [
        'flock_id', 'classification_id', 'category_id', 'lay_date', 
        'classification_date', 'quality', 'reject_reason', 'destination', 'traceability_code'
    ];

    protected $casts = [
        'lay_date' => 'date',
        'classification_date' => 'date',
    ];

    public function flock()
    {
        return $this->belongsTo(Flock::class);
    }

    public function classification()
    {
        return $this->belongsTo(EggClassification::class, 'classification_id');
    }

    public function category()
    {
        return $this->belongsTo(EggCategory::class, 'category_id');
    }

    public function inventory()
    {
        return $this->hasOne(EggInventory::class);
    }
}
