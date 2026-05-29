<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mcscr;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $tasks = Task::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('mcscr')
            ->orderBy('name','asc')
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
        $task = Task::create($data);
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
        $task = Task::with('mcscr')->find($id);

        $searchQuery = request('query');
        $mcscrs = Mcscr::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('reason','like',"%{$searchQuery}%")->orWhere('cause','like',"%{$searchQuery}%")->orWhere('consequence','like',"%{$searchQuery}%")->orWhere('solution','like',"%{$searchQuery}%")->orWhere('recommendation','like',"%{$searchQuery}%");
        })
        ->with('mcscr_status')
        ->with('equipment')
        ->with('reason_name')
        ->with('solution_name')
        ->with('consequence_name')
        ->with('recommendation_name')
        ->with('consequence_name')
        ->where('type_malfunction_id',$id)
        ->where('task_id',$id)
        ->orderBy('id','desc')
        ->paginate();


        return [
            'task'=>$task,
            'mcscrs'=>$mcscrs
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $task = Task::find($id);
       
        


        return $task ;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $task = Task::find($id);

        $task->update($data);

        return $task;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $task = Task::find($id);

        

        $mcscr = Mcscr::where('task_id',$id)->get();
        


        if($mcscr->count()>0){
            $msg = 'Não foi possivel apagar a tarefa.Existem MCSCR alocados a esta tarefa ';
        }
      

        if($mcscr->count()>0){

            return response()->json([
                'message' => $msg
            ], 404);

        }else{

            $task->delete();

            return true;
        }
        
    }
}
