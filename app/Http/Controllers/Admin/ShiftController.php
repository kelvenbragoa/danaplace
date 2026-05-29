<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupShift;
use App\Models\Shift;
use App\Models\ShiftEquipmentRequest;
use App\Models\TypeEquipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $shifts = Shift::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('user')
            ->orderBy('date','desc')
            ->paginate();

            return $shifts;
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
        $userid = ['user_id'=> Auth::user()->id ];

        
        $shift = Shift::create(array_merge($data,$userid));

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
        $shift = Shift::with('user')->find($id);
        $groups = GroupShift::where('shift_id',$id)->with('groupshiftoperators')->get();
        $type_equipments = TypeEquipment::orderBy('name','asc')->get();
        $requests = ShiftEquipmentRequest::with('typeequipment')->where('shift_id',$id)->get();


        return [
            'shift'=>$shift,
            'groups'=>$groups,
            'type_equipments'=>$type_equipments,
            'requests'=>$requests
        
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $shift = Shift::with('user')->find($id);
        


        return $shift;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();
        $shift = Shift::find($id);
        $shift->update($data);

        return $shift;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $shift = Shift::find($id);

        $shift->delete();

        return true;
    }
}
