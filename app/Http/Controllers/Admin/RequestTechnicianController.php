<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequestTechnician;
use App\Models\RequestTechnicianItem;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestTechnicianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

        $requesttechnician = RequestTechnician::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('id','like',"%{$searchQuery}%");
        })
        ->with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->orderBy('id','desc')
        ->paginate();
        return $requesttechnician;
        
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

        if($data['type_task'] == 1){
            $requesttechnician = RequestTechnician::create([
                'mcscr_id'=>$data['request_id'],
                'first_observation'=>$data['first_observation'],
                'created_by_user_id'=>Auth::user()->id,
                'request_technician_status_id'=>1
            ]);
        }else{
            $requesttechnician = RequestTechnician::create([
                'task_mcscr_id'=>$data['request_id'],
                'first_observation'=>$data['first_observation'],
                'created_by_user_id'=>Auth::user()->id,
                'request_technician_status_id'=>1
            ]);
        }

        
    
        

        foreach ($data['departments'] as $item){
            RequestTechnicianItem::create([
                'request_technician_id'=>$requesttechnician->id,
                'department_id'=>$item['department_id'],
                'obs'=>$item['obs']
            ]);
        }
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
        $requesttechnician = RequestTechnician::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->find($id);

        $departments = RequestTechnicianItem::
        where('request_technician_id',$id)
        ->with('technician')
        ->with('department.technicians')
        ->orderBy('id','asc')->get();

        return [
            'requesttechnician'=>$requesttechnician,
            'departments'=>$departments
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $requesttechnician = RequestTechnician::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->find($id);

        $departments = RequestTechnicianItem::
        where('request_technician_id',$id)
        ->with('technician')
        ->with('department.technicians_available')
        ->orderBy('id','asc')->get();

        return [
            'requesttechnician'=>$requesttechnician,
            'departments'=>$departments
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();

        $requesttechnician = RequestTechnician::find($id);

        if($data['request_status'] == 2 || $data['request_status'] == 3){
            $requesttechnician->update([
                'approved_by_user_id'=>Auth::user()->id,
                'approved_date'=>now(),
                'request_technician_status_id'=>$data['request_status']
            ]);
        }
        
        if($data['request_status'] == 4){
           
          
            $requesttechnician->update([
                'delivered_by_user_id'=>Auth::user()->id,
                'delivered_date'=>now(),
                'request_technician_status_id'=>$data['request_status']
            ]);

            if($request->has('requesttechnicianitens')){

            foreach ($data['requesttechnicianitens'] as $item){
                
                $requesttechnicianitem = RequestTechnicianItem::find($item['item_id']);
                $technician = Technician::find($item['technician_id']);

                if($technician->status == 1){
                    $requesttechnicianitem->update([
                        'technician_id'=>$item['technician_id'],
                    ]);
                   
                    
    
                    $technician->update([
                        'status'=>0
                    ]);
                }

                
            }
        }

        }

        return $requesttechnician;
    }

    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $requesttechnician = RequestTechnician::find($id);
        $requesttechnician->delete();
        return true;
    }
}
