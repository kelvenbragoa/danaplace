<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criticaly;
use App\Models\EquipmentComponent;
use App\Models\EquipmentStatus;
use App\Models\EquipmentSubComponent;
use Illuminate\Http\Request;

class EquipmentSubComponentController extends Controller
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

        $equipmentsubcomponents = EquipmentSubComponent::where('equipment_component_id',$data['equipment_component_id'])->sum('percentage_weigth');

        $percentage = $data['percentage_weigth'];
        
        if( $equipmentsubcomponents+$percentage  > 100){
            return response()->json([
                'message' => 'Não foi possivel adicionar este componente porque excede a percentagem de 100%',
            ], 404);
           
        }
       



        
        $equipment_component = EquipmentSubComponent::create([
            'name'=>$data['name'],
            'ref'=>$data['ref'],
            'criticaly_id'=>$data['criticaly_id'],
            'equipment_id'=>$data['equipment_id'],
            'equipment_component_id'=>$data['equipment_component_id'],
            'equipment_status_id'=>$data['equipment_status_id'],
            'percentage_weigth'=>$data['percentage_weigth'],
            'model'=>$data['model'],
            'make'=>$data['make'],
            'serial'=>$data['serial'],
            
            
        ]);

        $component = EquipmentComponent::with('equipmentstatus')->with('criticality')->find($data['equipment_component_id']);

        $subcomponents = EquipmentSubComponent::with('criticality')
        ->with('equipmentstatus')
        ->where('equipment_component_id',$data['equipment_component_id'])
        ->paginate();

        $criticals = Criticaly::get();
        $equipmentstatuses = EquipmentStatus::get();
        


        return [
            'component'=>$component,
            'subcomponents'=>$subcomponents,
            'criticals'=>$criticals,
            'equipmentstatuses'=>$equipmentstatuses
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //

        $subcomponent = EquipmentSubComponent::with('criticality')->with('equipmentstatus')->find($id);
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
        $subcomponent = EquipmentSubComponent::with('criticality')->with('equipmentstatus')->find($id);
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

        $subcomponent = EquipmentSubComponent::find($id);

        $data = $request->all();

        $equipmentsubcomponents = EquipmentSubComponent::where('equipment_component_id',$subcomponent->equipment_component_id)->sum('percentage_weigth');

        $equipmentsubcomponents = $equipmentsubcomponents - $subcomponent->percentage_weigth;

        $percentage = $data['percentage_weigth'];

        if( $equipmentsubcomponents+$percentage  > 100){
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
        $component = EquipmentSubComponent::find($id);
        $component->delete();
        return response()->noContent();
    }
}
