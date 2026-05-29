<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupShiftOperators;
use App\Models\Shift;
use App\Models\ShiftEquipmentRequest;
use App\Models\ShiftEquipmentRequestItem;
use Illuminate\Http\Request;

class ShiftEquipmentRequestItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $requestitem = ShiftEquipmentRequestItem::with('equipment')->find($id);
        $shift = Shift::find($requestitem->shift_id);
        $shiftrequest = ShiftEquipmentRequest::with('typeequipment')->find($requestitem->shift_equipment_request_id);
        $users = GroupShiftOperators::with('user')->where('shift_id',$requestitem->shift_id)->get();

        return [
            'shift'=>$shift,
            'requestitem'=>$requestitem,
            'shiftrequest'=>$shiftrequest,
            'users'=>$users
        ];
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //

        $data = $request->all();

        $shiftrequestitem = ShiftEquipmentRequestItem::find($id);

        $shiftrequestitem->update($data);

        return $shiftrequestitem;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
