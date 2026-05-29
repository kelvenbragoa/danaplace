<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\User;
use App\Notifications\Operation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class InspectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');
        $status = request('status');

            $inspections = Inspection::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('equipment_id','like',"%{$searchQuery}%");
            })
            ->when(request('status'),function($query,$status){
                $query->where('inspection_status_id',$status);
            })
            ->when(request('destination'),function($query,$destination){
                $query->where('destination_id',$destination);
            })
            ->with('inspection_status')
            ->with('equipment')
            // ->orderBy('opened_at','desc')
            ->orderBy('opened_at','desc')
            ->paginate();

            return [
                'inspection'=>$inspections,
                'total' => Inspection::count(),
                'programado' => Inspection::where('inspection_status_id',1)->count(),
                'executado' => Inspection::where('inspection_status_id',2)->count(),
                'destinations'=>Destination::get()
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


        $inspection = Inspection::create([
            'equipment_id'=>$equipment->id,
            'destination_id'=>$equipment->destination_id,
            'area_id'=>$equipment->area_id,
            'type_equipment_id'=>$equipment->type_equipment_id,
            'inspection_status_id'=>$data['inspection_status_id'],
            'is_operational'=>$data['is_operational'] ?? null,
            'total_hours'=>$data['total_hours'] ?? null,

            'engine_condition'=>$data['engine_condition'] ?? null,
            'engine_description'=>$data['engine_description'] ?? null,

            'eletrical_system_condition'=>$data['eletrical_system_condition'] ?? null,
            'eletrical_system_description'=>$data['eletrical_system_description'] ?? null,

            'transmission_condition'=>$data['transmission_condition'] ?? null,
            'transmission_description'=>$data['transmission_description'] ?? null,

            'control_system_condition'=>$data['control_system_condition'] ?? null,
            'control_system_description'=>$data['control_system_description'] ?? null,

            'structure_condition'=>$data['structure_condition'] ?? null,
            'structure_description'=>$data['structure_description'] ?? null,

            'hydraulic_system_condition'=>$data['hydraulic_system_condition'] ?? null,
            'hydraulic_system_description'=>$data['hydraulic_system_description'] ?? null,

            'pneumatic_system_condition'=>$data['pneumatic_system_condition'] ?? null,
            'pneumatic_system_description'=>$data['pneumatic_system_description'] ?? null,

            'suspension_condition'=>$data['suspension_condition'] ?? null,
            'suspension_description'=>$data['suspension_description'] ?? null,

            'tyres_condition'=>$data['tyres_condition'] ?? null,
            'tyres_description'=>$data['tyres_description'] ?? null,

            'blades_condition'=>$data['blades_condition'] ?? null,
            'blades_description'=>$data['blades_description'] ?? null,

            'cabin_condition'=>$data['cabin_condition'] ?? null,
            'cabin_description'=>$data['cabin_description'] ?? null,

            'others_condition'=>$data['others_condition'] ?? null,
            'others_description'=>$data['others_description'] ?? null,

            'rating_unit_condition'=>$data['rating_unit_condition'] ?? null,
            'rating_in_operation'=>$data['rating_in_operation'] ?? null,

            'comments'=>$data['comments'] ?? null,
            'recommendation_1'=>$data['recommendation_1'] ?? null,

            'recommendation_2'=>$data['recommendation_2'] ?? null,
            'recommendation_3'=>$data['recommendation_3'] ?? null,

            'recommendation_4'=>$data['recommendation_4'] ?? null,
            'inspection_status_id'=>$data['inspection_status_id'] ?? null,

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
        $inspection = Inspection::
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
            'inspection'=>$inspection,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $inspection = Inspection::
        with('equipment')
        ->with('equipment.destination')
        ->with('equipment.area')
        ->with('inspection_status')
        ->with('equipment.type_equipment')
        ->find($id);
        return [
            'inspection'=>$inspection
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();
        $inspection = Inspection::find($id);
        
        $inspection->update($data);
        return $inspection;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
