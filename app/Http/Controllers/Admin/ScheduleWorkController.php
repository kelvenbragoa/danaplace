<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\ScheduleWork;
use App\Models\ScheduleWorkItem;
use App\Models\Technician;
use App\Models\TypeEquipment;
use Illuminate\Http\Request;

class ScheduleWorkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $schedulework = ScheduleWork::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->orderBy('date','desc')
            ->paginate();

            return $schedulework;
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

        // dd($data);
        $schedulework = ScheduleWork::create($data);
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
        $schedulework = ScheduleWork::
        find($id);
        $typeequipments = TypeEquipment::orderBy('name','asc')->get();
        $departments = Department::orderBy('name','asc')->get();
        $scheduleworkitens = ScheduleWorkItem::
        with('equipment')
        ->with('technician')
        ->where('schedule_works_id',$id)->orderBy('start_time')->paginate();

      


        return [
            'schedulework'=>$schedulework,
            'departments'=>$departments,
            'typeequipments'=>$typeequipments,
            'scheduleworkitens'=>$scheduleworkitens
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $schedulework = ScheduleWork::find($id);
        


        return $schedulework;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $schedulework = ScheduleWork::find($id);

        $schedulework->update($data);

        return $schedulework;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $schedulework = ScheduleWork::find($id);

        $schedulework->delete();

        return true;
    }
}
