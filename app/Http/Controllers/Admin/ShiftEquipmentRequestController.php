<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\GroupShift;
use App\Models\Shift;
use App\Models\ShiftEquipmentRequest;
use App\Models\ShiftEquipmentRequestItem;
use App\Models\TypeEquipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftEquipmentRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

        $shiftequipmentrequest = ShiftEquipmentRequest::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('id','like',"%{$searchQuery}%");
        })
        ->with('typeequipment')
        ->with('createdbyuser')
        ->with('answeredbyuser')
        ->with('shift')
        ->orderBy('id','desc')
        ->paginate();
        return $shiftequipmentrequest;
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

        $shift = Shift::with('user')->find($data['shift_id']);

        $groups = GroupShift::where('shift_id',$data['shift_id'])->with('groupshiftoperators')->get();
        $type_equipments = TypeEquipment::orderBy('name','asc')->get();

        $shiftequipmentrequest = ShiftEquipmentRequest::create([
            'shift_id'=>$data['shift_id'],
            'type_equipment_id'=>$data['type_equipment_id'],
            'request_quantity'=>$data['request_quantity'],
            'status'=>0,
            'created_by_user_id'=>Auth::user()->id,
            'obs'=>$data['obs'],
        ]);

        $requests = ShiftEquipmentRequest::with('typeequipment')->where('shift_id',$data['shift_id'])->get();



        return [
            'shift'=>$shift,
            'groups'=>$groups,
            'type_equipments'=>$type_equipments,
            'requests'=>$requests
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $request = ShiftEquipmentRequest::with('typeequipment')
        ->with('typeequipment')
        ->with('createdbyuser')
        ->with('answeredbyuser')
        ->with('shift')
        ->find($id);

        $equipments = Equipment::with('type_equipment')->where('type_equipment_id',$request->type_equipment_id)->where('equipment_status_id',1)->get();

        $requestitens = ShiftEquipmentRequestItem::with('equipment')->with('useroperator')->where('shift_equipment_request_id',$request->id)->get();

        return [
            'request'=>$request,
            'equipments'=>$equipments,
            'requestitens'=>$requestitens
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $request = ShiftEquipmentRequest::with('typeequipment')
        ->with('typeequipment')
        ->with('createdbyuser')
        ->with('answeredbyuser')
        ->with('shift')
        ->find($id);

        $equipments = Equipment::with('type_equipment')->where('type_equipment_id',$request->type_equipment_id)->where('equipment_status_id',1)->get();

        return [
            'request'=>$request,
            'equipments'=>$equipments
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //

        $data = $request->all();

        $requestequipment = ShiftEquipmentRequest::find($id);


        

        $requestequipment->update([
            'delivered_quantity'=>$data['delivered_quantity'],
            'status'=>1,
            'answered_by_user_id'=>Auth::user()->id,
            'answered_date'=>now(),
        ]);

        if($request->has('requestequipmentitem')){

            foreach ($data['requestequipmentitem'] as $item){
                
                ShiftEquipmentRequestItem::create([
                    'shift_id'=>$requestequipment->shift_id,
                    'shift_equipment_request_id'=>$data['request_equipment_id'],
                    'type_equipment_id'=>$requestequipment->type_equipment_id,
                    'equipment_id'=>$item['equipment_id'],
                ]);
                
            }
        }

        return $requestequipment;


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $request = ShiftEquipmentRequest::find($id);

        $request->delete();
    }
}
