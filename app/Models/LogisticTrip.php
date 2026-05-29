<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogisticTrip extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
    
    public function destination(): BelongsTo
    {
        return $this->belongsTo(LogisticTripDestination::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(LogisticCustomer::class);
    }
    public function tripstatus(): BelongsTo
    {
        return $this->belongsTo(LogisticTripStatus::class,'trip_status_id','id');
    }
}
