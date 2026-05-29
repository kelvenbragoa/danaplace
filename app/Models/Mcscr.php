<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mcscr extends Model
{
    use HasFactory;
    // protected $fillable = [
    //         'equipment_id',
    //         'type_equipment_id',
    //         'equipment_component_id',
    //         'equipment_sub_component_id',
    //         'destination_id',
    //         'area_id',
    //         'reason_id',
    //         'reason',
    //         'cause_id',
    //         'task_id',
    //         'cause',
    //         'solution_id',
    //         'solution',
    //         'consequence_id',
    //         'consequence',
    //         'recommendation_id',
    //         'recommendation',
    //         'waiting_status_id',
    //         'type_malfunction_id',
    //         'mcscr_status_id',
    //         'opened_by_user_id',
    //         'closed_by_user_id',
    //         'opened_at',
    //         'closed_at',
    //         'output_forecast',
    //         'is_rework',
    //         'first_observation',
    //         'last_observation',
    //         'distance',
    //         'material_cost',
    //         'material_labor',
    //         'total_hours',
    //         'diagnosis_start_at',
    //         'diagnosis_end_at',
    //         'execution_start_at',
    //         'execution_end_at',
    //         'awaiting_approval_start_at',
    //         'awaiting_approval_end_at'
    // ];

    protected $guarded = [];

    public function mcscr_status(){
        return $this->hasOne('App\Models\McscrStatus', 'id', 'mcscr_status_id');
    }

    public function equipment(){
        return $this->hasOne('App\Models\Equipment', 'id', 'equipment_id');
    }

    public function component(){
        return $this->hasOne('App\Models\EquipmentComponent', 'id', 'equipment_component_id');
    }

    public function subcomponent(){
        return $this->hasOne('App\Models\EquipmentSubComponent', 'id', 'equipment_sub_component_id');
    }
    public function reason_name(){
        return $this->hasOne('App\Models\Reason', 'id', 'reason_id');
    }

    public function cause_name(){
        return $this->hasOne('App\Models\Cause', 'id', 'cause_id');
    }

    public function solution_name(){
        return $this->hasOne('App\Models\Solution', 'id', 'solution_id');
    }

    public function consequence_name(){
        return $this->hasOne('App\Models\Consequence', 'id', 'consequence_id');
    }

    public function recommendation_name(){
        return $this->hasOne('App\Models\Recommendation', 'id', 'recommendation_id');
    }

    public function type_malfunction(){
        return $this->hasOne('App\Models\TypeMalfunction', 'id', 'type_malfunction_id');
    }

    public function opened_by_user(){
        return $this->hasOne('App\Models\User', 'id', 'opened_by_user_id');
    }

    public function closed_by_user(){
        return $this->hasOne('App\Models\User', 'id', 'closed_by_user_id');
    }

    public function area(){
        return $this->hasOne('App\Models\Area', 'id', 'area_id');
    }

    public function destination(){
        return $this->hasOne('App\Models\Destination', 'id', 'destination_id');
    }

    public function waiting_status(){
        return $this->hasOne('App\Models\WaitingStatus', 'id', 'waiting_status_id');
    }
}
