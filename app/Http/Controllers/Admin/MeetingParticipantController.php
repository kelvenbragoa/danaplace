<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\MeetingTask;
use App\Models\User;
use Illuminate\Http\Request;

class MeetingParticipantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $users = User::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('firstName','like',"%{$searchQuery}%")->orWhere('lastName','like',"%{$searchQuery}%")->orWhere('email','like',"%{$searchQuery}%");
            })
            ->when(request('destination'),function($query,$destination){
                $query->where('destination_id',$destination);
            })
            ->with('role')
            ->with('area')
            ->with('destination')
            ->with('country')
            ->with('province')
            ->with('city')
            ->with('user_status')
            ->with('account_status')
            ->with('taskdone')
            ->with('tasknotdone')
            ->orderBy('firstName','asc')
            ->paginate();

            $destinations = Destination::all();


            return response()->json([
                'meetingparticipant'=>$users,
                'destinations' => $destinations
            ]);
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
        $meeting = Meeting::find($data['meeting_id']);

        if($data['user_source'] == 1){
            foreach($data['participants'] as $item){
                $test=  MeetingParticipant::where('user_id',$item['participant_id'])->where('meeting_id',$data['meeting_id'])->first();
                if($test == null){
                    $user = User::find($item['participant_id']);
                        MeetingParticipant::create([
                            'meeting_id'=>$data['meeting_id'],
                            'user_id'=>$user->id,
                            'role_id'=>$user->role_id,
                            'email'=>$user->email,
                            'name'=>$user->firstName.' '.$user->lastName,
                            'obs'=>$data['obs'] ?? '',
                            'source'=>$data['user_source'],
                            'email_status'=>0,
                            'status'=>1,
                        ]);
                }
            }
            $participants = MeetingParticipant::with('role')->where('meeting_id',$data['meeting_id'])->get();
            return response()->json([
                'message'=>'success',
                'participants'=>$participants
            ],201);
        }else{
            if($data['email'] != null && $data['name'] != null){
                $test=  MeetingParticipant::where('email',$data['email'])->where('meeting_id',$data['meeting_id'])->first();
                if($test == null){
                    
                    MeetingParticipant::create([
                        'meeting_id'=>$data['meeting_id'],
                        'email'=>$data['email'],
                        'name'=>$data['name'],
                        'obs'=>$data['obs'] ?? '',
                        'source'=>$data['user_source'],
                        'email_status'=>0,
                        'status'=>1,

                    ]);

                    $participants = MeetingParticipant::with('role')->where('meeting_id',$data['meeting_id'])->get();
                    return response()->json([
                        'message'=>'success',
                        'participants'=>$participants
                    ],201);
                }
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $user = User::with('role')
        ->with('area')
        ->with('destination')
        ->with('country')
        ->with('province')
        ->with('city')
        ->with('user_status')
        ->with('account_status')->find($id);

        $tasks = MeetingTask::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('what','like',"%{$searchQuery}%");
        })
        ->where('user_id',$user->id)
        ->with('participant')
        ->with('meeting')
        ->with('status')
        ->orderBy('created_at','asc')
        ->get(); 

        $meetings = MeetingParticipant::where('user_id',$user->id)->count();

        $totaltasks = MeetingTask::where('user_id',$user->id)->count();
        $totaldonetasks = MeetingTask::where('user_id',$user->id)->where('status_id',1)->count();
        $totalnotdonetasks = MeetingTask::where('user_id',$user->id)->where('status_id',2)->count();



        return response()->json([
            'user'=>$user,
            'tasks'=>$tasks,
            'meetings'=>$meetings,
            'totaltasks'=>$totaltasks,
            'totaldonetasks'=>$totaldonetasks,
            'totalnotdonetasks'=>$totalnotdonetasks,
        ]);
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

        $participant = MeetingParticipant::find($id);

        $participant->update([
            'obs'=>$data['obs']
        ]);

        $participants = MeetingParticipant::where('meeting_id',$participant->meeting_id)->get();

        return response()->json([
            'participants' => $participants
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $meeting = MeetingParticipant::find($id);

        $tasks = MeetingTask::where('meeting_participant_id', $id)->count();

        if($tasks > 0){
            return response()->json([],404);
        }

        $meeting->delete();

        return true;
    }
}
