<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\TypeOfMeeting;
use Illuminate\Http\Request;

class MeetingTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $typemeeting = TypeOfMeeting::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->orderBy('name','asc')
            ->paginate();

            return $typemeeting;
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
        $typemeeting = TypeOfMeeting::create($data);
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
        $typemeeting = TypeOfMeeting::find($id);

      


        return [
            'meetingtype'=>$typemeeting,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $typemeeting = TypeOfMeeting::find($id);
        


        return $typemeeting;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $typemeeting = TypeOfMeeting::find($id);

        $typemeeting->update($data);

        return $typemeeting;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $typemeeting = TypeOfMeeting::find($id);

        $meeting = Meeting::where('type_meeting_id',$id)->count();

        if($meeting>0){
            return response()->json([],404);
        }

        $typemeeting->delete();

        return true;
    }
}
