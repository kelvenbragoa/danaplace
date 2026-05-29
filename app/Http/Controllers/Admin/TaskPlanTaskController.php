<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criticaly;
use App\Models\Department;
use App\Models\Frequency;
use App\Models\Product;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\TaskPlan;
use App\Models\TaskPlanEquipment;
use App\Models\TaskPlanTask;
use App\Models\TaskPlanTaskDepartment;
use App\Models\TaskPlanTaskHumanResource;
use App\Models\TaskPlanTaskMaterial;
use App\Models\TypeEquipment;
use App\Models\TypeSubTask;
use Illuminate\Http\Request;

class TaskPlanTaskController extends Controller
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

        $request->validate([

        ]);

        TaskPlanTask::create([
            'name'=>$data['name'],
            'type_task_id'=>$data['type_task_id'],
            'critical_id'=>$data['critical_id'],
            'frequency_id'=>$data['frequency_id'],
            'task_plan_id'=>$data['task_plan_id'],
            'do_every'=>$data['do_every'],
            'estimated_time_days'=>$data['estimated_time_days'],
            'estimated_time_hours'=>$data['estimated_time_hours'],
            'estimated_time_minutes'=>$data['estimated_time_minutes'],
            'unavailable_equipment_time_days'=>$data['unavailable_equipment_time_days'],
            'unavailable_equipment_time_hours'=>$data['unavailable_equipment_time_hours'],
            'unavailable_equipment_time_minutes'=>$data['unavailable_equipment_time_minutes'],

        ]);

        $taskplan = TaskPlan::find($data['task_plan_id']);
            $type_equipments = TypeEquipment::orderBy('name','asc')->get();
            $taskplanequipments = TaskPlanEquipment::where('task_plan_id',$data['task_plan_id'])->with('equipment')->paginate();
            $typetask = Task::orderBy('name','asc')->get();
            $criticals = Criticaly::orderBy('id','asc')->get();
            $frequencies = Frequency::orderBy('id','asc')->get();
            $taskplantasks = TaskPlanTask::where('task_plan_id',$data['task_plan_id'])->with('typetask')->with('frequency')->with('critical')->orderBy('do_every')->paginate();

            return [
                'taskplan'=>$taskplan,
                'type_equipments'=>$type_equipments,
                'taskplanequipments'=>$taskplanequipments,
                'typetasks'=>$typetask,
                'criticals'=>$criticals,
                'frequencies'=>$frequencies,
                'taskplantasks'=>$taskplantasks
            ];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $taskplantasks = TaskPlanTask::
        with('critical')
        ->with('frequency')
        ->with('typetask')
        ->with('subtasks')
        ->find($id);

        $subtasks = SubTask::where('task_plan_task_id',$id)->with('typesubtask')->orderBy('id','desc')->paginate();
        $typesubtasks = TypeSubTask::orderBy('id','desc')->get();
        $products = Product::orderBy('name','asc')->limit(50)->get();
        $departments = Department::orderBy('name','asc')->get();
        $taskproducts = TaskPlanTaskMaterial::where('task_plan_task_id',$id)->with('product')->limit(50)->get();
        $taskdepartments = TaskPlanTaskDepartment::where('task_plan_task_id',$id)->with('department')->get();

        return [
            'taskplantasks'=>$taskplantasks,
            'subtasks'=>$subtasks,
            'typesubtasks'=>$typesubtasks,
            'products'=>$products,
            'departments'=>$departments,
            'taskproducts'=>$taskproducts,
            'taskdepartments'=>$taskdepartments
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $taskplantasks = TaskPlanTask::
        with('critical')
        ->with('frequency')
        ->with('typetask')
        ->find($id);
        $typetasks = Task::orderBy('name','asc')->get();
        $criticals = Criticaly::orderBy('id','asc')->get();
        $frequencies = Frequency::orderBy('id','asc')->get();

        return [
            'taskplantasks'=>$taskplantasks,
            'typetasks'=>$typetasks,
            'frequencies'=>$frequencies,
            'criticals'=>$criticals,
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();

        $taskplantasks = TaskPlanTask::find($id);

        $taskplantasks->update($data);

        return  $taskplantasks;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $taskplantask = TaskPlanTask::find($id);

       

        $taskplantask->delete();

        return response()->noContent();
    }


    public function copytask(string $id){

        
        $taskplantasks = TaskPlanTask::
        with('critical')
        ->with('frequency')
        ->with('typetask')
        ->with('subtasks')
        ->find($id);

       

        $oldsubtasks = SubTask::where('task_plan_task_id',$id)->get();
        $oldmaterial = TaskPlanTaskMaterial::where('task_plan_task_id',$id)->get();
        $oldhuman = TaskPlanTaskDepartment::where('task_plan_task_id',$id)->get();

        // $subTask = SubTask::create($data);


        $newtaskplantask = TaskPlanTask::create([
            'name'=>'COPY-'.$taskplantasks->name,
            'type_task_id'=>$taskplantasks->type_task_id,
            'critical_id'=>$taskplantasks->critical_id,
            'frequency_id'=>$taskplantasks->frequency_id,
            'task_plan_id'=>$taskplantasks->task_plan_id,
            'do_every'=>$taskplantasks->do_every,
            'estimated_time_days'=>$taskplantasks->estimated_time_days,
            'estimated_time_hours'=>$taskplantasks->estimated_time_hours,
            'estimated_time_minutes'=>$taskplantasks->estimated_time_minutes,
            'unavailable_equipment_time_days'=>$taskplantasks->unavailable_equipment_time_days,
            'unavailable_equipment_time_hours'=>$taskplantasks->unavailable_equipment_time_hours,
            'unavailable_equipment_time_minutes'=>$taskplantasks->unavailable_equipment_time_minutes,
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
      
        $taskplantasksold = TaskPlanTask::where('task_plan_id',$taskplantasks->task_plan_id)->with('typetask')->with('frequency')->with('critical')->paginate();

        return [
            'taskplantasks'=>$taskplantasksold,
            
        ];


    }
}
