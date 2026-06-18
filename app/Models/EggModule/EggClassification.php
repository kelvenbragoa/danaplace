<?php

namespace App\Models\EggModule;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EggClassification extends Model
{
    use HasFactory;
    // protected $table = 'egg_classifications';
    
    protected $fillable = [
        'flock_id', 'processing_date', 'washed_eggs', 
        'unwashed_eggs', 'total_rejects', 'reject_percentage', 'responsible_id'
    ];

    protected $casts = [
        'processing_date' => 'date',
    ];

    public function flock()
    {
        return $this->belongsTo(Flock::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function eggs()
    {
        return $this->hasMany(Egg::class, 'classification_id');
    }

    public function packaging()
    {
        return $this->hasMany(Packing::class);
    }
    
    public function getTotalProcessedAttribute()
    {
        return $this->washed_eggs + $this->unwashed_eggs;
    }
}
