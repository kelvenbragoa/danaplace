<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Equipment;
use App\Models\EquipmentComponent;
use App\Models\EquipmentSubComponent;
use App\Models\JobCardRecommendationTask;
use App\Models\Mcscr;
use App\Models\McscrResolution;
use App\Models\TaskMcscr;
use App\Models\User;
use App\Notifications\Operation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class JobCardRecommendationTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function mcscrjobtask(){
        $searchQuery = request('query');
        $status = request('status');

        $destination = request('destination');

            $mcscrs = Mcscr::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('reason','like',"%{$searchQuery}%")->orWhere('cause','like',"%{$searchQuery}%")->orWhere('consequence','like',"%{$searchQuery}%")->orWhere('solution','like',"%{$searchQuery}%")->orWhere('recommendation','like',"%{$searchQuery}%");
            })
            ->when(request('status'),function($query,$status){
                $query->where('mcscr_status_id',$status);
            })
            ->when(request('destination'),function($query,$destination){
                $query->where('destination_id',$destination);
            })
            ->with('destination')
            ->with('mcscr_status')
            ->with('equipment')
            ->with('reason_name')
            ->with('solution_name')
            ->with('consequence_name')
            ->with('recommendation_name')
            ->with('consequence_name')
            ->where('is_generated_by_task',1)
            // ->orderBy('opened_at','desc')
            ->orderBy('opened_at','desc')
            ->paginate(100);

            return [
                'mcscr'=>$mcscrs,
                'total' => Mcscr::where('is_generated_by_task',1)->count(),
                'terminado' => Mcscr::where('mcscr_status_id',1)->where('is_generated_by_task',1)->count(),
                'pendente' => Mcscr::where('mcscr_status_id',4)->where('is_generated_by_task',1)->count(),
                'programado' => Mcscr::where('mcscr_status_id',6)->where('is_generated_by_task',1)->count(),
                'aprovacao' => Mcscr::where('mcscr_status_id',2)->where('is_generated_by_task',1)->count(),
                'diagnostico' => Mcscr::where('mcscr_status_id',5)->where('is_generated_by_task',1)->count(),
                'execucao' => Mcscr::where('mcscr_status_id',3)->where('is_generated_by_task',1)->count(),
                'destinations'=>Destination::orderBy('name')->get()
            ];
    }
    public function index()
    {
        //
        $searchQuery = request('query');

            $jobs = JobCardRecommendationTask::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('task','like',"%{$searchQuery}%");
            })
            ->when(request('destination'),function($query,$destination){
                $query->where('destination_id',$destination);
            })
            ->with('type_equipment')
            ->with('equipment')
            ->with('status')
            ->with('area')
            ->with('destination')
            ->orderBy('created_at','desc')
            ->paginate();

            return response()->json([
                'jobs'=>$jobs,
                'destinations' =>Destination::get()
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

        $job = JobCardRecommendationTask::find($data['job_task_id']);

        // dd($data);

        $taskmcscr_test = TaskMcscr::where('equipment_id',$job->equipment_id)->where('task_mcscr_status_id',3)->get();
        $mcscr_test = Mcscr::where('equipment_id',$job->equipment_id)->where('mcscr_status_id','!=',1)->get();

      

       if($data['mcscr_status_id'] == 4){
        if($mcscr_test->count() > 0){
            return response()->json([
                'message' => 'Não foi possível adicionar o MCSCR para este equipamento. Existe um MCSCR em execução, termine e volte a tentar novamente.',
            ], 404);
        }

        if($taskmcscr_test->count() > 0){
            return response()->json([
                'message' => 'Não foi possível adicionar o MCSCR para este equipamento. Existe uma Atividade Planeada em execução, termine e volte a tentar novamente.',
            ], 404);
        }
       }
        

       

        $request->validate([
            'opened_at' =>'required',
            'output_forecast' =>'required',
            'equipment_component_id' =>'required',
            'type_malfunction_id' =>'required',
            'task_id' =>'required',
            'distance' =>'required',    
        ]);

        $equipment = Equipment::find($job->equipment_id);

        if($data['mcscr_status_id'] == 4){
            if($data['equipment_component_id'] != 0 ){
                $equipment_component = EquipmentComponent::find($data['equipment_component_id']);
                $equipment_component->update([
                    'equipment_status_id'=>2
                ]);
            }
    
            if($data['equipment_sub_component_id'] != 0 ){
                $equipment_sub_component = EquipmentSubComponent::find($data['equipment_sub_component_id']);
                $equipment_sub_component->update([
                    'equipment_status_id'=>2
                ]);
            }
            
            $equipment->update([
                'equipment_status_id'=>2
            ]);
        }

        




        $mcscr = Mcscr::create([
            'reason'=>$job->task,
            'reason_id'=>0,

            'cause_id'=>0,
            'solution_id'=>0,
            'consequence_id'=>0,
            'recommendation_id'=>0,


            'opened_at'=>$data['opened_at'],
            'is_rework'=>0,
            'output_forecast'=>$data['output_forecast'],
            'type_equipment_id'=>$equipment->type_equipment_id,
            'equipment_id'=>$equipment->id,
            'destination_id'=>$equipment->destination_id,
            'area_id'=>$equipment->area_id,
            'equipment_component_id'=>$data['equipment_component_id'] == 0 ? null : $data['equipment_component_id'],
            'equipment_sub_component_id'=>$data['equipment_sub_component_id'] == 0 ? null : $data['equipment_sub_component_id'],
            'type_malfunction_id'=>$data['type_malfunction_id'],
            'task_id'=>$data['task_id'],
            'opened_by_user_id'=>Auth::user()->id,
            'mcscr_status_id'=>$data['mcscr_status_id'],
            'distance'=>$data['distance'],
            'waiting_status_id'=>4,
            'first_observation'=> $data['first_observation'],
            'is_generated_by_task'=> 1
        ]);

        if($request->has('mcscrs')){
            foreach ($data['mcscrs'] as $item){
                    McscrResolution::create([
                        'mcscr_id'=>$mcscr->id,
                        'resolution_name'=>$item['resolution_name'],
                    ]);
            }
        }

        $job->update([
            'status_id'=>5,
            'generated_mcscr_id'=>$mcscr->id
        ]);

        $msg = 'Foi aberto um novo MCSCR para o Equipamento '.$equipment->name.'('.$equipment->ref.').';
        $user = User::all();
        Notification::send($user,new Operation($msg));
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
        $job = JobCardRecommendationTask::
        with('type_equipment')
            ->with('equipment')
            ->with('status')
            ->with('area')
            ->with('destination')
        ->find($id);



        return $job;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $job = JobCardRecommendationTask::
        with('type_equipment')
            ->with('equipment')
            ->with('status')
            ->with('area')
            ->with('destination')
        ->find($id);



        return $job;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();

        $job = JobCardRecommendationTask::find($id);
        
        $status = $job->status_id == 1 ? 2 : 1;

        $job->update([
            'status_id'=>$status
        ]);

        return $job;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
