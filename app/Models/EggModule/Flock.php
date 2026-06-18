<?php

namespace App\Models\EggModule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flock extends Model
{
    use HasFactory;
    use SoftDeletes;

    // protected $table = 'flocks';
    
    protected $fillable = [
        'house_id', 'lineage_id', 'code', 'birth_date', 'housing_date',
        'initial_bird_count', 'current_bird_count', 'expected_disposal_date', 
        'actual_disposal_date', 'status', 'observations'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'housing_date' => 'date',
        'expected_disposal_date' => 'date',
        'actual_disposal_date' => 'date',
    ];

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function lineage()
    {
        return $this->belongsTo(Lineage::class);
    }

    public function dailyProduction()
    {
        return $this->hasMany(DailyProduction::class);
    }

    public function mortality()
    {
        return $this->hasMany(Mortality::class);
    }

    public function vaccinationSchedule()
    {
        return $this->hasMany(VaccineSchedule::class);
    }

    public function eggClassifications()
    {
        return $this->hasMany(EggClassification::class);
    }

    public function eggs()
    {
        return $this->hasMany(Egg::class);
    }

    // Accessors
    public function getAgeDaysAttribute()
    {
        return $this->housing_date->diffInDays(now());
    }

    public function getLayingRateAttribute()
    {
        if ($this->current_bird_count == 0) return 0;
        
        $todayProduction = $this->dailyProduction()
            ->where('date', now()->toDateString())
            ->first();
            
        if (!$todayProduction) return 0;
        
        return ($todayProduction->total_eggs / $this->current_bird_count) * 100;
    }
    
    public function getMortalityRateAttribute()
    {
        if ($this->initial_bird_count == 0) return 0;
        $deaths = $this->initial_bird_count - $this->current_bird_count;
        return ($deaths / $this->initial_bird_count) * 100;
    }
}
