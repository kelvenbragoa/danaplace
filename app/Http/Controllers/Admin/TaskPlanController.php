<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criticaly;
use App\Models\Destination;
use App\Models\Equipment;
use App\Models\Frequency;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\TaskPlan;
use App\Models\TaskPlanEquipment;
use App\Models\TaskPlanTask;
use App\Models\TaskPlanTaskDepartment;
use App\Models\TaskPlanTaskMaterial;
use App\Models\TypeEquipment;
use Illuminate\Http\Request;

class TaskPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $taskplans = TaskPlan::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('equipments')
            ->with('taskplantasks')
            ->orderBy('id','desc')
            ->paginate();

            return $taskplans;
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
        $taskplan = TaskPlan::create($data);
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
        $taskplan = TaskPlan::find($id);
        $type_equipments = TypeEquipment::orderBy('name','asc')->get();
        $taskplanequipments = TaskPlanEquipment::where('task_plan_id',$id)->with('equipment')->paginate();

        $typetasks = Task::orderBy('name','asc')->get();
        $criticals = Criticaly::orderBy('id','asc')->get();
        $frequencies = Frequency::orderBy('id','asc')->get();
        $taskplantasks = TaskPlanTask::where('task_plan_id',$id)->with('typetask')->with('frequency')->with('critical')->orderBy('id','desc')->paginate();
        $destinations = Destination::orderBy('name','asc')->get();



        return [
            'taskplan'=>$taskplan,
            'type_equipments'=>$type_equipments,
            'taskplanequipments'=>$taskplanequipments,
            'typetasks'=>$typetasks,
            'criticals'=>$criticals,
            'frequencies'=>$frequencies,
            'taskplantasks'=>$taskplantasks,
            'destinations'=>$destinations
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $taskplan = TaskPlan::find($id);
       
        


        return $taskplan ;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $taskplan = TaskPlan::find($id);

        $taskplan->update($data);

        return $taskplan;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $taskplan = TaskPlan::find($id);
        $taskplan->delete();
        return true;
    }

    public function copytask(string $id){

        $taskplanequipments = TaskPlanEquipment::where('task_plan_id',$id)->with('equipment')->get();
        $taskplantasks = TaskPlanTask::where('task_plan_id',$id)->get();



        $oldtaskplan = TaskPlan::find($id);

        $newtaskplan = TaskPlan::create([
            'name'=>$oldtaskplan->name.' Copy',
        ]);

        foreach ($taskplanequipments as $itemtaskplanequipment) {
            TaskPlanEquipment::create([
                'task_plan_id'=>$newtaskplan->id,
                'equipment_id'=>$itemtaskplanequipment->equipment_id,
                'type_equipment_id'=>$itemtaskplanequipment->type_equipment_id
            ]);
        }

        foreach ($taskplantasks as $itemtaskplantasks) {
            $oldsubtasks = SubTask::where('task_plan_task_id',$itemtaskplantasks->id)->get();
            $oldmaterial = TaskPlanTaskMaterial::where('task_plan_task_id',$itemtaskplantasks->id)->get();
            $oldhuman = TaskPlanTaskDepartment::where('task_plan_task_id',$itemtaskplantasks->id)->get();

            $newtaskplantask = TaskPlanTask::create([
                'name'=>$itemtaskplantasks->name,
                'type_task_id'=>$itemtaskplantasks->type_task_id,
                'critical_id'=>$itemtaskplantasks->critical_id,
                'frequency_id'=>$itemtaskplantasks->frequency_id,
                'task_plan_id'=>$newtaskplan->id,
                'do_every'=>$itemtaskplantasks->do_every,
                'estimated_time_days'=>$itemtaskplantasks->estimated_time_days,
                'estimated_time_hours'=>$itemtaskplantasks->estimated_time_hours,
                'estimated_time_minutes'=>$itemtaskplantasks->estimated_time_minutes,
                'unavailable_equipment_time_days'=>$itemtaskplantasks->unavailable_equipment_time_days,
                'unavailable_equipment_time_hours'=>$itemtaskplantasks->unavailable_equipment_time_hours,
                'unavailable_equipment_time_minutes'=>$itemtaskplantasks->unavailable_equipment_time_minutes,
            ]);

            foreach ($oldsubtasks as $itemsubtask){
                SubTask::create([
                    'name'=>$itemsubtask->name,
                    'task_plan_task_id'=>$newtaskplantask->id,
                    'type_sub_task_id'=>$itemsubtask->type_sub_task_id,
                ]);
            }
            foreach ($oldmaterial as $itemmaterial){
                TaskPlanTaskMaterial::create([
                    'product_id'=>$itemmaterial->product_id,
                    'task_plan_task_id'=>$newtaskplantask->id,
                    'quantity'=>$itemmaterial->quantity,
                    'product_name'=>$itemmaterial->product_name,
    
                ]);
            }
            foreach ($oldhuman as $itemhuman){
                TaskPlanTaskDepartment::create([
                    'quantity'=>$itemhuman->quantity,
                    'task_plan_task_id'=>$newtaskplantask->id,
                    'department_id'=>$itemhuman->department_id,
                    'department_name'=>$itemhuman->department_name
                ]);
            }
        }
        


        $taskplans = TaskPlan::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%");
        })
        ->with('equipments')
        ->with('taskplantasks')
        ->orderBy('id','desc')
        ->paginate();

        return $taskplans;

    }
}
