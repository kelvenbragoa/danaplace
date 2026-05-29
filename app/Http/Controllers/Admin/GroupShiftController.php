<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupShift;
use App\Models\GroupShiftOperators;
use App\Models\Shift;
use App\Models\ShiftEquipmentRequest;
use App\Models\TypeEquipment;
use App\Models\User;
use Illuminate\Http\Request;

class GroupShiftController extends Controller
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

        $data = $request->all();

        $shift = Shift::with('user')->find($data['shift_id']);

        

        $group = GroupShift::create($data);

        $groups = GroupShift::where('shift_id',$data['shift_id'])->with('groupshiftoperators')->get();
        $type_equipments = TypeEquipment::orderBy('name','asc')->get();
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

        
        $group = GroupShift::with('shift.user')->with('groupshiftoperators')->find($id);
        $operators = GroupShiftOperators::where('shift_id',$group->shift_id)->where('group_shift_id',$id)->with('user')->get();
        $operatorsUser = User::all();
        $type_equipments = TypeEquipment::orderBy('name','asc')->get();



        return [
            
            'group'=>$group,
            'operators'=>$operators,
            'operatorsUser'=>$operatorsUser,
            'type_equipments'=>$type_equipments
        
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $group = GroupShift::find($id);

        $group->delete();

    }
}
