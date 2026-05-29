<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criticaly;
use App\Models\Frequency;
use App\Models\Task;
use App\Models\TaskPlan;
use App\Models\TaskPlanEquipment;
use App\Models\TaskPlanTask;
use App\Models\TypeEquipment;
use Illuminate\Http\Request;

class TaskPlanEquipmentController extends Controller
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

        if($request->has('planequipments')){

        foreach($data['planequipments'] as $item){
            if(count($item) > 0){
                $equipment_test = TaskPlanEquipment::where('type_equipment_id',$data['type_equipment_id'])->where('equipment_id',$item['equipment_id'])->get();
                if($equipment_test->count() == 0){
                    TaskPlanEquipment::create([
                        'task_plan_id'=>$data['task_plan_id'],
                        'equipment_id'=>$item['equipment_id'],
                        'type_equipment_id'=>$data['type_equipment_id'],
                    ]);
        
                    
                }
            }
            
        }

        $taskplan = TaskPlan::find($data['task_plan_id']);
                    $type_equipments = TypeEquipment::orderBy('name','asc')->get();
                    $taskplanequipments = TaskPlanEquipment::where('task_plan_id',$data['task_plan_id'])->with('equipment')->paginate();
                    $typetasks = Task::orderBy('name','asc')->get();
                    $criticals = Criticaly::orderBy('id','asc')->get();
                    $frequencies = Frequency::orderBy('id','asc')->get();
                    $taskplantasks = TaskPlanTask::where('task_plan_id',$data['task_plan_id'])->with('typetask')->with('frequency')->with('critical')->orderBy('do_every')->paginate();
        
                    return [
                        'taskplan'=>$taskplan,
                        'type_equipments'=>$type_equipments,
                        'taskplanequipments'=>$taskplanequipments,
                        'typetasks'=>$typetasks,
                        'criticals'=>$criticals,
                        'frequencies'=>$frequencies,
                        'taskplantasks'=>$taskplantasks
                    ];

        

        
            
        }else{
            return response()->json([
                'message' => 'Nenhum equipamento selecionado',
            ], 404);
        }

        

        // $equipment_test = TaskPlanEquipment::where('task_plan_id',$data['task_plan_id'])->where('type_equipment_id',$data['type_equipment_id'])->where('equipment_id',$data['equipment_id'])->get();
        // $equipment_test = TaskPlanEquipment::where('type_equipment_id',$data['type_equipment_id'])->where('equipment_id',$data['equipment_id'])->get();


        // if($equipment_test->count() == 0){
        //     TaskPlanEquipment::create([
        //         'task_plan_id'=>$data['task_plan_id'],
        //         'equipment_id'=>$data['equipment_id'],
        //         'type_equipment_id'=>$data['type_equipment_id'],
        //     ]);

        //     $taskplan = TaskPlan::find($data['task_plan_id']);
        //     $type_equipments = TypeEquipment::orderBy('name','asc')->get();
        //     $taskplanequipments = TaskPlanEquipment::where('task_plan_id',$data['task_plan_id'])->with('equipment')->paginate();
        //     $typetasks = Task::orderBy('name','asc')->get();
        //     $criticals = Criticaly::orderBy('id','asc')->get();
        //     $frequencies = Frequency::orderBy('id','asc')->get();
        //     $taskplantasks = TaskPlanTask::where('task_plan_id',$data['task_plan_id'])->with('typetask')->with('frequency')->with('critical')->paginate();

        //     return [
        //         'taskplan'=>$taskplan,
        //         'type_equipments'=>$type_equipments,
        //         'taskplanequipments'=>$taskplanequipments,
        //         'typetasks'=>$typetasks,
        //         'criticals'=>$criticals,
        //         'frequencies'=>$frequencies,
        //         'taskplantasks'=>$taskplantasks
        //     ];

        // }else{

        //     return response()->json([
        //         'message' => 'Não foi possivel associar este equipamento/ativo porque está associado um plano de tarefas. "'.$equipment_test[0]->taskplan->name.'"',
        //     ], 404);
        // }
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

        $equipment = TaskPlanEquipment::find($id);

       

        $equipment->delete();

        return response()->noContent();
    }
}
