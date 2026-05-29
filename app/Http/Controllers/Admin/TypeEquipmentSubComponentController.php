<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criticaly;
use App\Models\EquipmentStatus;
use App\Models\TypeEquipment;
use App\Models\TypeEquipmentComponent;
use App\Models\TypeEquipmentSubComponent;
use Illuminate\Http\Request;

class TypeEquipmentSubComponentController extends Controller
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

        $typeequipmentsubcomponents = TypeEquipmentSubComponent::where('type_equipment_component_id',$data['type_equipment_component_id'])->sum('percentage_weigth');

        $percentage = $data['percentage_weigth'];
        
        if( $typeequipmentsubcomponents+$percentage  > 100){
            return response()->json([
                'message' => 'Não foi possivel adicionar este SUBcomponente porque excede a percentagem de 100%',
            ], 404);
           
        }
       



        
        $type_equipment_sub_component = TypeEquipmentSubComponent::create([
            'name'=>$data['name'],
            'criticaly_id'=>$data['criticaly_id'],
            'type_equipment_component_id'=>$data['type_equipment_component_id'],
            'percentage_weigth'=>$data['percentage_weigth'],
            'model'=>$data['model'],
            'make'=>$data['make'],
        ]);

        $component = TypeEquipmentComponent::
        with('criticality')
        ->with('subcomponents')
        ->find($data['type_equipment_component_id']);


        $criticals = Criticaly::get();

        // $subcomponents = TypeEquipmentSubComponent::query()
        // ->when(request('query'),function($query,$searchQuery){
        //     $query->where('name','like',"%{$searchQuery}%");
        // })
        // ->with('criticality')
        // ->where('type_equipment_component_id',$data['type_equipment_component_id'])
        // ->orderBy('name','asc')
        // ->paginate();

        

        $subcomponents = TypeEquipmentSubComponent::where('type_equipment_component_id',$data['type_equipment_component_id']) ->with('criticality')->orderBy('name','asc') ->paginate();

        return[
            'subcomponents' =>$subcomponents,
            'criticals'=>$criticals,
            'component'=>$component
            ];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $subcomponent = TypeEquipmentSubComponent::with('criticality')->find($id);
        $criticals = Criticaly::orderBy('name','asc')->get();
        $equipmentstatuses = EquipmentStatus::orderBy('name','asc')->get();

        return [
            'subcomponent'=>$subcomponent,
            'criticals'=>$criticals,
            'equipmentstatuses'=>$equipmentstatuses
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $subcomponent = TypeEquipmentSubComponent::with('criticality')->find($id);
        $criticals = Criticaly::orderBy('name','asc')->get();
        $equipmentstatuses = EquipmentStatus::orderBy('name','asc')->get();

        return [
            'subcomponent'=>$subcomponent,
            'criticals'=>$criticals,
            'equipmentstatuses'=>$equipmentstatuses
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //

        $subcomponent = TypeEquipmentSubComponent::find($id);

        $data = $request->all();

        $typeequipmentsubcomponents = TypeEquipmentSubComponent::where('type_equipment_component_id',$subcomponent->type_equipment_component_id)->sum('percentage_weigth');

        $typeequipmentsubcomponents = $typeequipmentsubcomponents - $subcomponent->percentage_weigth;

        $percentage = $data['percentage_weigth'];

        if( $typeequipmentsubcomponents+$percentage  > 100){
            return response()->json([
                'message' => 'Não foi possivel editar este subcomponente porque excede a percentagem de 100%',
            ], 404);
           
        }
        
        $subcomponent->update($data);
        return $subcomponent;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $subcomponent = TypeEquipmentSubComponent::find($id);
        $subcomponent->delete();
        return response()->noContent();
    }
}
