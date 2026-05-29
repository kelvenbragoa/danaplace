<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criticaly;
use App\Models\Equipment;
use App\Models\EquipmentComponent;
use App\Models\EquipmentStatus;
use App\Models\EquipmentSubComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class EquipmentComponentsController extends Controller
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
        $imagePath = null;
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'ref' => 'nullable|string|max:255',
            'criticaly_id' => 'required|integer',
            'equipment_id' => 'required|integer',
            'percentage_weigth' => 'required|numeric|min:0|max:100',
            'model' => 'required|string|max:255',
            'make' => 'required|string|max:255',
            'serial' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:10240', // até 10MB
        ]);

        $equipmentcomponents = EquipmentComponent::where('equipment_id',$data['equipment_id'])->sum('percentage_weigth');

        $percentage = $data['percentage_weigth'];
        
        if( $equipmentcomponents+$percentage  > 100){
            return response()->json([
                'message' => 'Não foi possivel adicionar este componente porque excede a percentagem de 100%',
            ], 404);
           
        }
       


        if($request->has('image')){
            $files = $request->file('image');
            $imagePath = $files->store('equipment-component-attachment','s3');
        }
        
        $equipment_component = EquipmentComponent::create([
            'name'=>$data['name'],
            'ref'=>$data['ref'] ?? null,
            'criticaly_id'=>$data['criticaly_id'],
            'equipment_id'=>$data['equipment_id'],
            'equipment_status_id'=>1,
            'percentage_weigth'=>$data['percentage_weigth'],
            'model'=>$data['model'],
            'make'=>$data['make'],
            'serial'=>$data['serial'] ?? null,
            'image_url'=>$imagePath ?? null,
            
        ]);




        $components = EquipmentComponent::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%");
        })
        ->with('equipmentstatus')
        ->with('subcomponents')
        ->with('criticality')
        ->where('equipment_id',$data['equipment_id'])
        ->orderBy('name','asc')
        ->paginate();




        return [
            'components'=>$components,
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $component = EquipmentComponent::with('equipmentstatus')->with('criticality')->find($id);

        $subcomponents = EquipmentSubComponent::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%");
        })
        ->with('criticality')
        ->with('equipmentstatus')
        ->where('equipment_component_id',$id)
        ->paginate();

        $component_picture = null;

        if($component->image_url == null){
            $component_picture = '/files/img/sys/companylogo.png';
        }else{

            $component->image_url = Storage::disk('s3')->temporaryUrl(
                $component->image_url,
                now()->addMinutes(10),
                ['ResponseContentDisposition' => 'attachment']
            );

            $component_picture = $component->image_url;
        }


        $criticals = Criticaly::get();

        $equipmentstatuses = EquipmentStatus::get();
        
        return [
            'component'=>$component,
            'subcomponents'=>$subcomponents,
            'criticals'=>$criticals,
            'equipmentstatuses'=>$equipmentstatuses,
            'component_picture'=>$component_picture,
        ];

    }   

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $component = EquipmentComponent::with('equipmentstatus')->with('criticality')->find($id);
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
        $component = EquipmentComponent::find($id);

        $data = $request->all();

        dd($data);

        $equipmentcomponents = EquipmentComponent::where('equipment_id',$component->equipment_id)->sum('percentage_weigth');

        $percentage = $data['percentage_weigth'];

        $equipmentcomponents = $equipmentcomponents - $component->percentage_weigth;

        if( $equipmentcomponents+$percentage  > 100){
            return response()->json([
                'message' => 'Não foi possivel editar este componente porque excede a percentagem de 100%',
            ], 404);
           
        }

        if($request->has('image')){
            $files = $request->file('image');
            $imagePath = $files->store('equipment-component-attachment','s3');
            $data['image_url'] = $imagePath ?? $component->image_url;
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
        // $component = EquipmentComponent::find($id);
        // $component->delete();
        return response()->noContent();
    }
}
