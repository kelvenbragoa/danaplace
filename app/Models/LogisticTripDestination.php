<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogisticTripDestination extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function loadstatus(): BelongsTo
    {
        return $this->belongsTo(LogisticTripLoadStatus::class,'load_status_id','id');
    }

    public function expenses(){
        return $this->hasMany('App\Models\LogisticDestinationExpense','destination_id','id');
    }

    public function coin(){
        return $this->hasOne('App\Models\Coin', 'id', 'coin_id');
    }
}
