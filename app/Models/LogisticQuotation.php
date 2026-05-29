<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticQuotation extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function tripdestination(){
        return $this->hasOne('App\Models\LogisticTripDestination', 'id', 'destination_id');
    }

    public function customer(){
        return $this->hasOne('App\Models\LogisticCustomer', 'id', 'customer_id');
    }

    public function typeload(){
        return $this->hasOne('App\Models\LogisticTypeLoad', 'id', 'type_load_id');
    }

    public function status(){
        return $this->hasOne('App\Models\StatusLogisticQuotation', 'id', 'status_logistic_quotation_id');
    }

}

