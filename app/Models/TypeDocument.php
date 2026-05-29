<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeDocument extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function documents(){
        return $this->hasMany('App\Models\Document', 'type_document_id', 'id');
    }


}
