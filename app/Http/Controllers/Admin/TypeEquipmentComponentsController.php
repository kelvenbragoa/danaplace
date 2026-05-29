<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criticaly;
use App\Models\Equipment;
use App\Models\EquipmentStatus;
use App\Models\TypeEquipment;
use App\Models\TypeEquipmentComponent;
use App\Models\TypeEquipmentSubComponent;
use Illuminate\Http\Request;

class TypeEquipmentComponentsController extends Controller
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

        $typeequipmentcomponents = TypeEquipmentComponent::where('type_equipment_id',$data['type_equipment_id'])->sum('percentage_weigth');

        $percentage = $data['percentage_weigth'];
        
        if( $typeequipmentcomponents+$percentage  > 100){
            return response()->json([
                'message' => 'Não foi possivel adicionar este componente porque excede a percentagem de 100%',
            ], 404);
           
        }
       

        $type_equipment_component = TypeEquipmentComponent::create([
            'name'=>$data['name'],
            'criticaly_id'=>$data['criticaly_id'],
            'type_equipment_id'=>$data['type_equipment_id'],
            'percentage_weigth'=>$data['percentage_weigth'],
            'model'=>$data['model'],
            'make'=>$data['make'],
        ]);

        $typeequipment = TypeEquipment::
        with('equipments')
            ->with('available_equipments')
            ->with('unavailable_equipments')
            ->with('imobilized_equipments')
            ->with('components')
        ->find($data['type_equipment_id']);

        $components = TypeEquipmentComponent::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%");
        })
        ->with('criticality')
        ->with('subcomponents')
        ->where('type_equipment_id',$data['type_equipment_id'])
        ->orderBy('name','asc')
        ->paginate();

        $criticals = Criticaly::get();

        return [
            'type_equipment'=>$typeequipment,
            'components'=>$components,
            'criticals'=>$criticals,
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $component = TypeEquipmentComponent::with('criticality')->with('subcomponents')->find($id);
        $subcomponents = TypeEquipmentSubComponent::where('type_equipment_component_id',$id) ->with('criticality')->orderBy('name','asc') ->paginate();
        $criticals = Criticaly::get();

       
        return[
            'subcomponents' =>$subcomponents,
            'criticals'=>$criticals,
            'component'=>$component
            ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $component = TypeEquipmentComponent::with('criticality')->find($id);
        $criticals = Criticaly::orderBy('name','asc')->get();
        $equipmentstatuses = EquipmentStatus::orderBy('name','asc')->get();

        return [
            'component'=>$component,
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
        $component = TypeEquipmentComponent::find($id);

        $data = $request->all();

        $typeequipmentcomponents = TypeEquipmentComponent::where('type_equipment_id',$component->type_equipment_id)->sum('percentage_weigth');

        $typeequipmentcomponents = $typeequipmentcomponents - $component->percentage_weigth;

        $percentage = $data['percentage_weigth'];

        if( $typeequipmentcomponents+$percentage  > 100){
            return response()->json([
                'message' => 'Não foi possivel editar este componente porque excede a percentagem de 100%',
            ], 404);
        }
        
        $component->update($data);
        return $component;

        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    { 
        //
        $component = TypeEquipmentComponent::find($id);
        $component->delete();
        return response()->noContent();
    }


    public function copytypeequipmentcomponent(string $id){
        $old_type_equipment_component = TypeEquipmentComponent::find($id);

        $data = $old_type_equipment_component->toArray();
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        $data['name'] = 'COPY-' . $old_type_equipment_component->name;

        $typeequipmentcomponent = TypeEquipmentComponent::create($data);    

        $subcomponents = TypeEquipmentSubComponent::where('type_equipment_component_id',$id)->get();

        foreach ($subcomponents as $item){

            $type_equipment_component = TypeEquipmentSubComponent::create([
                'name'=>$item->name,
                'criticaly_id'=>$item->criticaly_id,
                'percentage_weigth'=>$item->percentage_weigth,
                'type_equipment_component_id'=>$typeequipmentcomponent->id,
                'model'=>$item->model,
                'make'=>$item->make,
                
            ]);
  
        }

        return response()->json([
            'equipment'=>$typeequipmentcomponent
        ],200);


}
}
