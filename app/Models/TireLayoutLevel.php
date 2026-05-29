<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TireLayoutLevel extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function tirelayoutlevelpositions(){
        return $this->hasMany('App\Models\TireLayoutLevelPosition', 'tire_layout_level_id', 'id');
    }

}
