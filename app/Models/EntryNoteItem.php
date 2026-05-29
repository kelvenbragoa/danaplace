<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntryNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_center_id',
        'entry_note_id',
        'product_id',
        'quantity',
        'last_quantity'

    ];
    
    public function stockcenter(){
        return $this->hasOne('App\Models\StockCenter', 'id', 'stock_center_id');
    }

    public function product(){
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }
}
