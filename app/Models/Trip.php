<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function expenses(){
        return $this->hasMany('App\Models\TripExpense','trip_id','id');
    }

    public function getTotalExpensesAttribute()
    {
        return $this->expenses->sum('amount');
    }
    protected $appends = ['total_expenses'];
}
