<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function typedocument(){
        return $this->hasOne('App\Models\TypeDocument', 'id', 'type_document_id');
    }
}
