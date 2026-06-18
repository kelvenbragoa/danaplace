<?php

namespace App\Models\EggModule;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mortality extends Model
{
    use HasFactory;
    // protected $table = 'mortality';
    
    protected $fillable = [
        'flock_id', 'date', 'quantity', 'probable_cause', 
        'necropsy_performed', 'necropsy_report', 'responsible_id'
    ];

    protected $casts = [
        'date' => 'date',
        'necropsy_performed' => 'boolean',
    ];

    public function flock()
    {
        return $this->belongsTo(Flock::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
}
