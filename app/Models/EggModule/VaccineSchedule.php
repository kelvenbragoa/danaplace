<?php

namespace App\Models\EggModule;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccineSchedule extends Model
{
    use HasFactory;
    // protected $table = 'vaccine_schedules';
    
    protected $fillable = [
        'flock_id', 'vaccine_id', 'scheduled_date', 'application_date', 
        'administration_route', 'dosage', 'responsible_id', 'status', 'observations'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'application_date' => 'date',
    ];

    public function flock()
    {
        return $this->belongsTo(Flock::class);
    }

    public function vaccine()
    {
        return $this->belongsTo(Vaccine::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function getDelayDaysAttribute()
    {
        if ($this->status == 'applied') return 0;
        return $this->scheduled_date->diffInDays(now(), false);
    }
}
