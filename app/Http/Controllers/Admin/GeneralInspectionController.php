<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\GeneralInspection;
use App\Models\User;
use App\Notifications\Operation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class GeneralInspectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');
        $status = request('status');

            $generalinspections = GeneralInspection::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('equipment_id','like',"%{$searchQuery}%");
            })
            ->when(request('status'),function($query,$status){
                $query->where('inspection_status_id',$status);
            })
            ->with('inspection_status')
            ->with('equipment')
            // ->orderBy('opened_at','desc')
            ->orderBy('opened_at','desc')
            ->paginate();

            return [
                'generalinspection'=>$generalinspections,
                'total' => GeneralInspection::count(),
                'programado' => GeneralInspection::where('inspection_status_id',1)->count(),
                'executado' => GeneralInspection::where('inspection_status_id',2)->count(),
            ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->all();
        // $request->validate([
        //     'reason' =>'required',
        //     'reason_id' =>'required',
        //     'opened_at' =>'required',
        //     'output_forecast' =>'required',
        //     'type_equipment_id' =>'required',
        //     'equipment_id' =>'required',
        //     'equipment_component_id' =>'required',
        //     'type_malfunction_id' =>'required',
        //     'task_id' =>'required',
        //     'distance' =>'required',    
        // ]);
        $equipment = Equipment::find($data['equipment_id']);


        $generalinspection = GeneralInspection::create([
            'equipment_id'=>$equipment->id,
            'destination_id'=>$equipment->destination_id,
            'area_id'=>$equipment->area_id,
            'type_equipment_id'=>$equipment->type_equipment_id,
            'inspection_status_id'=>$data['inspection_status_id'],
            'is_operational'=>$data['is_operational'] ?? null,
            'total_hours'=>$data['total_hours'] ?? null,

            'interior'=>$data['interior'] ?? null,
            'interior_description'=>$data['interior_description'] ?? null,

            'seats'=>$data['seats'] ?? null,
            'seats_description'=>$data['seats_description'] ?? null,

            'interior_trim_roof_lining_carpet'=>$data['interior_trim_roof_lining_carpet'] ?? null,
            'interior_trim_roof_lining_carpet_description'=>$data['interior_trim_roof_lining_carpet_description'] ?? null,

            'dashboard_cluster'=>$data['dashboard_cluster'] ?? null,
            'dashboard_cluster_description'=>$data['dashboard_cluster_description'] ?? null,

            'heating_ventilation'=>$data['heating_ventilation'] ?? null,
            'heating_ventilation_description'=>$data['heating_ventilation_description'] ?? null,

            'interior_control_unitis'=>$data['interior_control_unitis'] ?? null,
            'interior_control_unitis_description'=>$data['interior_control_unitis_description'] ?? null,

            'air_condition'=>$data['air_condition'] ?? null,
            'air_condition_description'=>$data['air_condition_description'] ?? null,

            'eletric_windows'=>$data['eletric_windows'] ?? null,
            'eletric_windows_description'=>$data['eletric_windows_description'] ?? null,

            'eletric_sunroof'=>$data['eletric_sunroof'] ?? null,
            'eletric_sunroof_description'=>$data['eletric_sunroof_description'] ?? null,

            'seat_heaters'=>$data['seat_heaters'] ?? null,
            'seat_heaters_description'=>$data['seat_heaters_description'] ?? null,

            'rims'=>$data['rims'] ?? null,
            'rims_description'=>$data['rims_description'] ?? null,

            'mechanical_doors'=>$data['mechanical_doors'] ?? null,
            'mechanical_doors_description'=>$data['mechanical_doors_description'] ?? null,

            'vehicle_body'=>$data['vehicle_body'] ?? null,
            'vehicle_body_description'=>$data['vehicle_body_description'] ?? null,

            'windows'=>$data['windows'] ?? null,
            'windows_description'=>$data['windows_description'] ?? null,

            'hang_on_parts'=>$data['hang_on_parts'] ?? null,
            'hang_on_parts_description'=>$data['hang_on_parts_description'] ?? null,

            'spare_wheel'=>$data['spare_wheel'] ?? null,
            'spare_wheel_description'=>$data['spare_wheel_description'] ?? null,

            'tires'=>$data['tires'] ?? null,
            'tires_description'=>$data['tires_description'] ?? null,

            'engine_oil'=>$data['engine_oil'] ?? null,
            'engine_oil_description'=>$data['engine_oil_description'] ?? null,

            'engine_cooling_system'=>$data['engine_cooling_system'] ?? null,
            'engine_cooling_system_description'=>$data['engine_cooling_system_description'] ?? null,

            'oil_loss_engine'=>$data['oil_loss_engine'] ?? null,
            'oil_loss_engine_description'=>$data['oil_loss_engine_description'] ?? null,

            'oil_loss_gear_box'=>$data['oil_loss_gear_box'] ?? null,
            'oil_loss_gear_box_description'=>$data['oil_loss_gear_box_description'] ?? null,

            'exhaust_system'=>$data['exhaust_system'] ?? null,
            'exhaust_system_description'=>$data['exhaust_system_description'] ?? null,

            'gearshift'=>$data['gearshift'] ?? null,
            'gearshift_description'=>$data['gearshift_description'] ?? null,

            'noise_levels_engine'=>$data['noise_levels_engine'] ?? null,
            'noise_levels_engine_description'=>$data['noise_levels_engine_description'] ?? null,

            'noise_levels_transmissions'=>$data['noise_levels_transmissions'] ?? null,
            'noise_levels_transmissions_description'=>$data['noise_levels_transmissions_description'] ?? null,

            'noise_levels_axles'=>$data['noise_levels_axles'] ?? null,
            'noise_levels_axles_description'=>$data['noise_levels_axles_description'] ?? null,

            'engine'=>$data['engine'] ?? null,
            'engine_description'=>$data['engine_description'] ?? null,

            'gearbox'=>$data['gearbox'] ?? null,
            'gearbox_description'=>$data['gearbox_description'] ?? null,

            'drivetrain'=>$data['drivetrain'] ?? null,
            'drivetrain_description'=>$data['drivetrain_description'] ?? null,

            'brake_fluid'=>$data['brake_fluid'] ?? null,
            'brake_fluid_description'=>$data['brake_fluid_description'] ?? null,

            'brakes'=>$data['brakes'] ?? null,
            'brakes_description'=>$data['brakes_description'] ?? null,

            'brake_system'=>$data['brake_system'] ?? null,
            'brake_system_description'=>$data['brake_system_description'] ?? null,

            'vehicle_undercarriage'=>$data['vehicle_undercarriage'] ?? null,
            'vehicle_undercarriage_description'=>$data['vehicle_undercarriage_description'] ?? null,

            'axles_suspension'=>$data['axles_suspension'] ?? null,
            'axles_suspension_description'=>$data['axles_suspension_description'] ?? null,

            'front_left'=>$data['front_left'] ?? null,
            'front_right'=>$data['front_right'] ?? null,

            'front_axle_weight'=>$data['front_axle_weight'] ?? null,
            'front_deceleration'=>$data['front_deceleration'] ?? null,

            'rear_left'=>$data['rear_left'] ?? null,
            'rear_right'=>$data['rear_right'] ?? null,

            'rear_axle_weight'=>$data['rear_axle_weight'] ?? null,
            'rear_deceleration'=>$data['rear_deceleration'] ?? null,

            'emergency_left'=>$data['emergency_left'] ?? null,
            'emergency_right'=>$data['emergency_right'] ?? null,

            'emergency_axle_weight'=>$data['emergency_axle_weight'] ?? null,
            'emergency_deceleration'=>$data['emergency_deceleration'] ?? null,

            'front_left_size'=>$data['front_left_size'] ?? null,
            'front_left_load'=>$data['front_left_load'] ?? null,

            'front_left_manufacture'=>$data['front_left_manufacture'] ?? null,
            'front_left_model'=>$data['front_left_model'] ?? null,

            'front_left_type'=>$data['front_left_type'] ?? null,
            'front_left_date'=>$data['front_left_date'] ?? null,

            'front_left_thread_depth'=>$data['front_left_thread_depth'] ?? null,
            'front_right_size'=>$data['front_right_size'] ?? null,

            'front_right_load'=>$data['front_right_load'] ?? null,
            'front_right_manufacture'=>$data['front_right_manufacture'] ?? null,

            'front_right_model'=>$data['front_right_model'] ?? null,
            'front_right_type'=>$data['front_right_type'] ?? null,

            'front_right_date'=>$data['front_right_date'] ?? null,
            'front_right_thread_depth'=>$data['front_right_thread_depth'] ?? null,

            'rear_left_size'=>$data['rear_left_size'] ?? null,
            'rear_left_load'=>$data['rear_left_load'] ?? null,

            'rear_left_manufacture'=>$data['rear_left_manufacture'] ?? null,
            'rear_left_model'=>$data['rear_left_model'] ?? null,

            'rear_left_type'=>$data['rear_left_type'] ?? null,
            'rear_left_date'=>$data['rear_left_date'] ?? null,

            'rear_left_thread_depth'=>$data['rear_left_thread_depth'] ?? null,
            'rear_right_size'=>$data['rear_right_size'] ?? null,

            'rear_right_load'=>$data['rear_right_load'] ?? null,
            'rear_right_manufacture'=>$data['rear_right_manufacture'] ?? null,

            'rear_right_model'=>$data['rear_right_model'] ?? null,
            'rear_right_type'=>$data['rear_right_type'] ?? null,

            'rear_right_date'=>$data['rear_right_date'] ?? null,
            'rear_right_thread_depth'=>$data['rear_right_thread_depth'] ?? null,

            'spare_size'=>$data['spare_size'] ?? null,
            'spare_load'=>$data['spare_load'] ?? null,

            'spare_manufacture'=>$data['spare_manufacture'] ?? null,
            'spare_model'=>$data['spare_model'] ?? null,

            'spare_type'=>$data['spare_type'] ?? null,
            'spare_date'=>$data['spare_date'] ?? null,

            'spare_thread_depth'=>$data['spare_thread_depth'] ?? null,
            'diagnostic'=>$data['diagnostic'] ?? null,

            'inspection_condition'=>$data['inspection_condition'] ?? null,
            'comments'=>$data['comments'] ?? null,

            'concluding_remarks'=>$data['concluding_remarks'] ?? null,


            'opened_by_user_id'=>Auth::user()->id,
            'closed_by_user_id'=>Auth::user()->id,

            'opened_at'=>$data['opened_at'],

            'closed_at'=>$data['closed_at'] ?? null,


        ]);
        $msg = 'Foi aberto uma nova inspeção para o Equipamento '.$equipment->name.'('.$equipment->ref.').';
        $user = User::all();
        Notification::send($user,new Operation($msg));
        return [
            'message'=>'success'
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $generalinspection = GeneralInspection::
        with('equipment')
        ->with('equipment.destination')
        ->with('equipment.area')
        ->with('equipment.type_equipment')
        ->with('area.province')
        ->with('destination.province')
        ->with('inspection_status')
        ->with('opened_by_user')
        ->find($id);

        return [
            'generalinspection'=>$generalinspection,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $generalinspection = GeneralInspection::
        with('equipment')
        ->with('equipment.destination')
        ->with('equipment.area')
        ->with('inspection_status')
        ->with('equipment.type_equipment')
        ->find($id);
        return [
            'generalinspection'=>$generalinspection
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();
        $generalinspection = GeneralInspection::find($id);
        
        $generalinspection->update($data);
        return $generalinspection;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
