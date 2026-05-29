<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class McscrResolution extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function mcscr_status(){
        return $this->hasOne('App\Models\McscrStatus', 'id', 'mcscr_status_id');
    }
}
