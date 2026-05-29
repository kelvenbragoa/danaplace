<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function destination(){
        return $this->hasOne('App\Models\Destination','id','destination_id');
    }

    public function status(){
        return $this->hasOne('App\Models\StatusQuotation','id','status_quotation_id');
    }

    public function coin(){
        return $this->hasOne('App\Models\Coin','id','coin_id');
    }

    public function itens(){
        return $this->hasMany('App\Models\QuotationItem','quotation_id','id');
    }
}
