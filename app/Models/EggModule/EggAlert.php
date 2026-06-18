<?php

namespace App\Models\EggModule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EggAlert extends Model
{
    use HasFactory;
    // protected $table = 'egg_alerts';
    
    protected $fillable = [
        'type', 'title', 'message', 'alert_datetime', 'read_datetime', 
        'resolved_datetime', 'status', 'email_sent', 'sms_sent', 'flock_id'
    ];

    protected $casts = [
        'alert_datetime' => 'datetime',
        'read_datetime' => 'datetime',
        'resolved_datetime' => 'datetime',
        'email_sent' => 'boolean',
        'sms_sent' => 'boolean',
    ];

    public function flock()
    {
        return $this->belongsTo(Flock::class);
    }
    
    public function markAsRead()
    {
        $this->update([
            'read_datetime' => now(),
            'status' => 'read'
        ]);
    }
    
    public function markAsResolved()
    {
        $this->update([
            'resolved_datetime' => now(),
            'status' => 'resolved'
        ]);
    }
}
