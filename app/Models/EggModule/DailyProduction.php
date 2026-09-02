<?php

namespace App\Models\EggModule;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyProduction extends Model
{
    use HasFactory;
    // protected $table = 'daily_production';
    
    protected $fillable = [
        'flock_id', 'date', 'total_eggs', 'cracked_eggs', 'dirty_eggs',
        'deformed_eggs', 'clean_eggs', 'normal_eggs', 'grande_eggs', 'jumbo_eggs',
        'feed_consumption_kg', 'water_consumption_liters', 'light_hours',
        'responsible_id', 'observations',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function flock()
    {
        return $this->belongsTo(Flock::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    // Accessors
    public function getUsableEggsAttribute()
    {
        return $this->total_eggs - ($this->cracked_eggs + $this->dirty_eggs + $this->deformed_eggs);
    }

    public function getCrackedPercentageAttribute()
    {
        if ($this->total_eggs == 0) return 0;
        return ($this->cracked_eggs / $this->total_eggs) * 100;
    }
    
    public function getFeedConversionAttribute()
    {
        if ($this->total_eggs == 0) return 0;
        return $this->feed_consumption_kg / $this->total_eggs;
    }
}
