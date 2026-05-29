<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\MeetingTask;
use App\Models\MeetingTaskStatus;
use App\Models\Role;
use App\Models\TypeOfMeeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $meeting = Meeting::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('completed_tasks')
            ->with('incompleted_tasks')
            ->with('waiting_approval_tasks')
            ->with('completed_out_of_time_tasks')
            ->with('typemeeting')
            ->with('participants')
            ->orderBy('date','desc')
            ->paginate();

            return $meeting;
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
        $created_by = ['created_by_user_id'=> Auth::user()->id];
        $meeting = Meeting::create(array_merge($data,$created_by));
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
        $meeting = Meeting::with('typemeeting')->find($id);
        $roles = Role::orderBy('name','asc')->get();
        $participants = MeetingParticipant::with('user')->with('role')->where('meeting_id',$meeting->id)->get();
        $tasks = MeetingTask::where('meeting_id',$meeting->id)->with('participant')->with('status')->get();
        $statuses = MeetingTaskStatus::get();


    

        return [
            'meeting'=>$meeting,
            'roles'=>$roles,
            'participants'=>$participants,
            'tasks'=>$tasks,
            'statuses'=>$statuses
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $meeting = Meeting::find($id);
        $typemeetings = TypeOfMeeting::get();
        


        return [
            'meeting'=>$meeting,
            'typemeetings'=>$typemeetings
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $meeting = Meeting::find($id);

        $meeting->update($data);

        return $meeting;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $meeting = Meeting::find($id);
        $task = MeetingTask::where('meeting_id', $meeting->id)->delete();
        $meeting->delete();

        return true;
    }
}
