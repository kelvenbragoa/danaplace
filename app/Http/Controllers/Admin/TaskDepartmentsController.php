<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Product;
use App\Models\SubTask;
use App\Models\TaskPlanTask;
use App\Models\TaskPlanTaskDepartment;
use App\Models\TaskPlanTaskMaterial;
use App\Models\TypeSubTask;
use Illuminate\Http\Request;

class TaskDepartmentsController extends Controller
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
        $taskplandepartment = TaskPlanTaskDepartment::create([
            'department_id'=>$data['department_id'],
            'quantity'=>$data['quantity_department'],
            'department_name'=>$data['department_name'],
            'task_plan_task_id'=>$data['task_plan_task_id']

        ]);


        $taskplantasks = TaskPlanTask::
        with('critical')
        ->with('frequency')
        ->with('typetask')
        ->with('subtasks')
        ->find($data['task_plan_task_id']);

        $subtasks = SubTask::where('task_plan_task_id',$data['task_plan_task_id'])->with('typesubtask')->paginate();
        $typesubtasks = TypeSubTask::orderBy('id','asc')->get();
        $products = Product::orderBy('name','asc')->get();
        $departments = Department::orderBy('name','asc')->get();
        $taskproducts = TaskPlanTaskMaterial::where('task_plan_task_id',$data['task_plan_task_id'])->with('product')->limit(50)->get();
        $taskdepartments = TaskPlanTaskDepartment::where('task_plan_task_id',$data['task_plan_task_id'])->with('department')->get();

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
        $department = TaskPlanTaskDepartment::find($id);
        $department->delete();
        return true;
    }
}
