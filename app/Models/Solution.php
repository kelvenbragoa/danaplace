<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solution extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code'
    ];

    public function mcscr(){
        return $this->hasMany('App\Models\Mcscr','solution_id','id');
    }
}
