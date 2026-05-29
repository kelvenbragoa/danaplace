<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequestTool;
use App\Models\RequestToolItem;
use App\Models\ToolShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestToolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

        $requesttool = RequestTool::query()
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
        return $requesttool;
        
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
            $requesttool = RequestTool::create([
                'mcscr_id'=>$data['request_id'],
                'first_observation'=>$data['first_observation'],
                'created_by_user_id'=>Auth::user()->id,
                'request_tool_status_id'=>1
            ]);
        }else{
            $requesttool = RequestTool::create([
                'task_mcscr_id'=>$data['request_id'],
                'first_observation'=>$data['first_observation'],
                'created_by_user_id'=>Auth::user()->id,
                'request_tool_status_id'=>1
            ]);
        }

        
    
        

        foreach ($data['tools'] as $item){
            $test_if_exists = RequestToolItem::where('request_tool_id',$requesttool->id)->where('tool_id',$item['tool_id'])->first();

            if($test_if_exists==null){
                RequestToolItem::create([
                    'request_tool_id'=>$requesttool->id,
                    'tool_id'=>$item['tool_id'],
                    'obs'=>$item['obs']
                ]);
            }
           
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
        $requesttool = RequestTool::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->find($id);

        $tools = RequestToolItem::
        where('request_tool_id',$id)
        ->with('tool')
        ->orderBy('id','asc')->get();

        return [
            'requesttools'=>$requesttool,
            'tools'=>$tools
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $requesttool = RequestTool::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->find($id);

        $tools = RequestToolItem::
        where('request_tool_id',$id)
        ->with('tool')
        ->orderBy('id','asc')->get();

        return [
            'requesttool'=>$requesttool,
            'tools'=>$tools
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();

        $requesttool = RequestTool::find($id);

        if($data['request_status'] == 2 || $data['request_status'] == 3){
            $requesttool->update([
                'approved_by_user_id'=>Auth::user()->id,
                'approved_date'=>now(),
                'request_tool_status_id'=>$data['request_status']
            ]);
        }
        
        if($data['request_status'] == 4){
           
          
            $requesttool->update([
                'delivered_by_user_id'=>Auth::user()->id,
                'delivered_date'=>now(),
                'request_tool_status_id'=>$data['request_status']
            ]);

            $tools = RequestToolItem::where('request_tool_id',$id)->get();

            

            foreach ($tools as $item){
                
                
                $tool = ToolShop::find($item->tool_id);

                 $tool->update([
                    'status'=>0
                    ]);
            }
        

        }

        return $requesttool;
    }

    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $requesttechnician = RequestTool::find($id);
        $requesttechnician->delete();
        return true;
    }
}
