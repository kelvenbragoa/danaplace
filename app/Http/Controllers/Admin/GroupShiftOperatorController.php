<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupShift;
use App\Models\GroupShiftOperators;
use App\Models\TypeEquipment;
use App\Models\User;
use Illuminate\Http\Request;

class GroupShiftOperatorController extends Controller
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



        if($request->has('users')){

            foreach($data['users'] as $item){
                if(count($item) > 0){
                    $operators_test = GroupShiftOperators::where('shift_id',$data['shift_id'])->where('user_id',$item['operator_id'])->get();
                    if($operators_test->count() == 0){
                        GroupShiftOperators::create([
                            'shift_id'=>$data['shift_id'],
                            'user_id'=>$item['operator_id'],
                            'group_shift_id'=>$data['group_shift_id'],
                        ]);
            
                        
                    }
                }
                
            }
    
            $group = GroupShift::with('shift.user')->with('groupshiftoperators')->find($data['group_shift_id']);
            $operators = GroupShiftOperators::where('shift_id',$group->shift_id)->where('group_shift_id',$data['group_shift_id'])->with('user')->get();
            $operatorsUser = User::all();
            $type_equipments = TypeEquipment::orderBy('name','asc')->get();

     



        return [
            
            'group'=>$group,
            'operators'=>$operators,
            'operatorsUser'=>$operatorsUser,
            'type_equipments'=>$type_equipments
        
        ];
    
            
    
            
                
            }else{
                return response()->json([
                    'message' => 'Nenhum equipamento selecionado',
                ], 404);
            }
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

        $operators = GroupShiftOperators::find($id);

        $operators->delete();
    }
}
