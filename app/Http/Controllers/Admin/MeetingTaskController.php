<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\MeetingTask;
use Illuminate\Http\Request;

class MeetingTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

        $tasks = MeetingTask::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('what','like',"%{$searchQuery}%");
        })
        ->with('participant')
        ->with('meeting')
        ->with('status')
        ->orderBy('created_at','asc')
        ->paginate();

        return $tasks;

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

        $participant = MeetingParticipant::find($data['participant_id']);

        $request->validate([
            'participant_id' => ['required'],
            'what' => ['required'],
            'date' => ['required'],
      
        ]);
        MeetingTask::create([
            'meeting_id'=>$data['meeting_id'],
            'role_id'=>$participant->role_id,
            'meeting_participant_id'=>$data['participant_id'],
            'what'=>$data['what'],
            'date'=>$data['date'],
            'status_id'=>2,

        ]);

        $tasks = MeetingTask::where('meeting_id',$data['meeting_id'])->with('participant')->with('status')->get();

        return response()->json([
            'message'=>'success',
            'tasks'=>$tasks
        ],201);
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
        $data = $request->all();

        $meetingtask = MeetingTask::find($id);

        $meetingtask->update($data);

        $tasks = MeetingTask::where('meeting_id', $meetingtask->meeting_id)->with('participant')->with('status')->get();

        return response()->json([
            'tasks'=>$tasks,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $meeting = MeetingTask::find($id);

        $meeting->delete();

        return true;
    }

    public function copy(Request $request){

        $data = $request->all();
        $task = MeetingTask::find($data['task_id']);

        if($request->has('participants')){

            foreach($data['participants'] as $item){
                $participant = MeetingParticipant::find($item['participant_id']);
                MeetingTask::create([
                    'meeting_id'=>$participant->meeting_id,
                    'role_id'=>$participant->role_id,
                    'meeting_participant_id'=>$item['participant_id'],
                    'user_id'=>$participant->user_id,
                    'what'=>$task->what,
                    'date'=>$task->date,
                    'status_id'=>2,
        
                ]);
            }
    }

    $tasks = MeetingTask::where('meeting_id',$task->meeting_id)->with('participant')->with('status')->get();

    return response()->json([
        'tasks'=>$tasks,
    ]);
}

public function calendar(){
    $tasks = MeetingTask::get()->map(function($task){
        return [
            'id'=>'taskk'.$task->id,
            'title'=>$task->what.'('.$task->participant->name.')',
            'date'=>$task->date,
            'start'=>$task->date,
            'backgroundColor'=>'#50B3C7',
            'borderColor'=>'#50B3C7',
            'color'=>'#50B3C7'
        ];
    });

    return $tasks;
}

public function detailcalendar($parms){
    $string = substr($parms, 5);
    $task = MeetingTask::with('status')->with('meeting')->with('participant')->find($string);


    
    return[
        'task'=>$task
    ];
}
}
