<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntryNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_center_id',
        'ref',
        'document_ref',
        'serie',
        'products_number',
        'stock_supplier_id',
        'obs',
        'user_id'
 
    ];

    public function stockcenter(){
        return $this->hasOne('App\Models\StockCenter', 'id', 'stock_center_id');
    }

    public function supplier(){
        return $this->hasOne('App\Models\StockSupplier', 'id', 'stock_supplier_id');
    }

    public function user(){
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function itens(){
        return $this->hasMany('App\Models\EntryNoteItem','entry_note_id','id');
    }

}
