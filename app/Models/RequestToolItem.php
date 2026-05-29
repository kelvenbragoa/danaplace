<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestToolItem extends Model
{
    use HasFactory;

    protected $fillable = [

        'request_tool_id',
        'tool_id',
        'obs',

    ];

    public function tool(){
        return $this->hasOne('App\Models\ToolShop', 'id', 'tool_id');
    }
}
