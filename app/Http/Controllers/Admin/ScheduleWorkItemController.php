<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\ScheduleWork;
use App\Models\ScheduleWorkItem;
use App\Models\TypeEquipment;
use Illuminate\Http\Request;

class ScheduleWorkItemController extends Controller
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

        $scheduleworkitem = ScheduleWorkItem::create([
            "equipment_id" =>$data['equipment_id'] ,
            "technician_id" =>$data['technician_id'] ,
            "start_time" =>$data['start_time'] ,
            "end_time" =>$data['end_time'] ,
            "schedule_works_id" =>$data['schedule_work_id'] ,
        ]);

        $schedulework = ScheduleWork::
        find($data['schedule_work_id']);
        $typeequipments = TypeEquipment::orderBy('name','asc')->get();
        $departments = Department::orderBy('name','asc')->get();
        $scheduleworkitens = ScheduleWorkItem::
        with('equipment')
        ->with('technician')
        ->where('schedule_works_id',$data['schedule_work_id'])->orderBy('start_time')->paginate();

        return [
            'schedulework'=>$schedulework,
            'departments'=>$departments,
            'typeequipments'=>$typeequipments,
            'scheduleworkitens'=>$scheduleworkitens
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

        $schedule = ScheduleWorkItem::find($id);

        $schedule->delete();

        return response()->noContent();
    }
}
