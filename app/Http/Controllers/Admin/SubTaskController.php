<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SubTask;
use App\Models\TaskPlanTask;
use App\Models\TaskPlanTaskMaterial;
use App\Models\TypeSubTask;
use Illuminate\Http\Request;

class SubTaskController extends Controller
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
        $subTask = SubTask::create($data);

        $taskplantasks = TaskPlanTask::
        with('critical')
        ->with('frequency')
        ->with('typetask')
        ->with('subtasks')
        ->find($data['task_plan_task_id']);

        $subtasks = SubTask::where('task_plan_task_id',$data['task_plan_task_id'])->with('typesubtask')->orderBy('name','asc')->paginate();
        $typesubtasks = TypeSubTask::orderBy('id','asc')->get();
        $products = Product::orderBy('name','asc')->get();
        $taskproducts = TaskPlanTaskMaterial::where('task_plan_task_id',$data['task_plan_task_id'])->with('product')->limit(50)->get();

        return [
            'taskplantasks'=>$taskplantasks,
            'subtasks'=>$subtasks,
            'typesubtasks'=>$typesubtasks,
            'products'=>$products,
            'taskproducts'=>$taskproducts
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

        
        $subTask = SubTask::find($id);
        $subTask->delete();
        return true;
    }
}
