<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'criticaly_id',
        'type_equipment_id',
        'equipment_status_id',
        'destination_id',
        'area_id',
        'supplier_id',
        'center_cost_account_id',
        'center_cost_id',
        'acquisition_id',
        'distance_control_id',
        'name',
        'ref',
        'make',
        'model',
        'serial',
        'chassis',
        'year',
        'buy_year',
        'load_unity_id',
        'load_max',
        'coin_id',
        'amount',
        'is_commissioned',
        'gps_tracking_id',
        'fuel',
        'is_building'
    ];

    public function area(){
        return $this->hasOne('App\Models\Area', 'id', 'area_id');
    }

    public function destination(){
        return $this->hasOne('App\Models\Destination', 'id', 'destination_id');
    }

    public function criticaly(){
        return $this->hasOne('App\Models\Criticaly', 'id', 'criticaly_id');
    }

    public function type_equipment(){
        return $this->hasOne('App\Models\TypeEquipment', 'id', 'type_equipment_id');
    }

    public function distance_control(){
        return $this->hasOne('App\Models\DistanceControl', 'id', 'distance_control_id');
    }
    public function equipment_status(){
        return $this->hasOne('App\Models\EquipmentStatus', 'id', 'equipment_status_id');
    }

    public function supplier(){
        return $this->hasOne('App\Models\Supplier', 'id', 'supplier_id');
    }

    public function center_cost(){
        return $this->hasOne('App\Models\CenterCost', 'id', 'center_cost_id');
    }

    public function acquisition(){
        return $this->hasOne('App\Models\Acquisition', 'id', 'acquisition_id');
    }

    public function center_cost_account(){
        return $this->hasOne('App\Models\CenterCostAccount', 'id', 'center_cost_account_id');
    }

    public function load_unity(){
        return $this->hasOne('App\Models\LoadUnity', 'id', 'load_unity_id');
    }

    public function coin(){
        return $this->hasOne('App\Models\Coin', 'id', 'coin_id');
    }

    public function lastmcscr(){
        return $this->hasOne('App\Models\Mcscr','equipment_id', 'id')->orderBy('id','desc')->latest();
    }

    public function mcscr(){
        return $this->hasMany('App\Models\Mcscr', 'equipment_id', 'id');
    }

    public function fees(){
        return $this->hasMany('App\Models\EquipmentFee', 'equipment_id', 'id');
    }

    public function feeInvoiceItems(){
        return $this->hasMany(FeeInvoiceItem::class);
    }

    public function activeFees(){
        return $this->belongsToMany(Fee::class, 'equipment_fees', 'equipment_id', 'fee_id');
    }

    public function mcscrmonth(){
        // return $this->hasMany('App\Models\Mcscr', 'equipment_id', 'id')->whereMonth('opened_at',date('M'))->whereYear('opened_at',date('Y'));
        return $this->hasMany('App\Models\Mcscr', 'equipment_id', 'id')->whereMonth('opened_at', '=', date('m'))->whereYear('opened_at', '=', date('Y'));
    }

    public function taskmcscrmonth(){
        // return $this->hasMany('App\Models\Mcscr', 'equipment_id', 'id')->whereMonth('opened_at',date('M'))->whereYear('opened_at',date('Y'));
        return $this->hasMany('App\Models\TaskMcscr', 'equipment_id', 'id')->whereMonth('opened_at', '=', date('m'))->whereYear('opened_at', '=', date('Y'));
    }


    public function lastdistance(){
        return $this->hasOne('App\Models\HoursDistanceEquipment','equipment_id', 'id')->orderBy('id','desc')->latest();
    }

    
}
