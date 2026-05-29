<?php

namespace App\Http\Controllers\Destination;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Equipment;
use App\Models\Mcscr;
use App\Models\RequestStock;
use App\Models\RequestStockItem;
use App\Models\RequestTechnician;
use App\Models\RequestTechnicianItem;
use App\Models\RequestTool;
use App\Models\TaskMcscr;
use App\Models\TaskMcscrDepartment;
use App\Models\TaskMcscrMaterial;
use App\Models\TaskMcscrStatus;
use App\Models\TaskMcscrSubTask;
use App\Models\TaskPlanTask;
use App\Models\Technician;
use App\Models\ToolShop;
use App\Models\User;
use App\Notifications\Operation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Barryvdh\DomPDF\Facade\Pdf;

class TaskMcscrController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $taskmcscrs = TaskMcscr::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('observation','like',"%{$searchQuery}%");
            })
            ->when(request('status'),function($query,$status){
                $query->where('task_mcscr_status_id',$status);
            })
            ->with('task_mcscr_status')
            ->with('equipment')
            ->with('closed_by_user')
            ->with('opened_by_user')
            ->with('schedule_by_user')
            ->with('area')
            ->with('destination')
            ->with('task_plan')
            ->with('task_plan_task')
            ->with('subtasks')
            ->where('destination_id',Auth::user()->destination_id)
            ->orderBy('id','desc')
            ->paginate();

            return [
                'taskmcscr'=>$taskmcscrs,
                'total' => TaskMcscr::where('destination_id',Auth::user()->destination_id)->count(),
                'terminado' => TaskMcscr::where('task_mcscr_status_id',2)->where('destination_id',Auth::user()->destination_id)->count(),
                'programado' => TaskMcscr::where('task_mcscr_status_id',4)->where('destination_id',Auth::user()->destination_id)->count(),
                'execucao' => TaskMcscr::where('task_mcscr_status_id',3)->where('destination_id',Auth::user()->destination_id)->count(),
            ];
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
        $equipment = Equipment::find($data['equipment_id']);
        $task_plan_task = TaskPlanTask::find($data['task_plan_task_id']);

        // $test_task_mcscr = TaskMcscr::where('equipment_id',$data['equipment_id'])
        // ->where('task_plan_task_id',$data['task_plan_task_id'])
        // ->where('task_mcscr_status_id','!=',2)
        // ->get();

      
       

        // if($test_task_mcscr->count() == 0){

            try {

                $taskmcscr = TaskMcscr::create([
                    'equipment_id'=>$data['equipment_id'],
                    'type_equipment_id'=>$equipment->type_equipment_id,
                    'task_plan_task_id'=>$data['task_plan_task_id'],
                    'schedule_for'=>$data['schedule_for'],
                    // 'closed_at'=>$data['closed_at'],
                    'observation'=>$data['observation'],
                    'destination_id'=>$equipment->destination_id,
                    'area_id'=>$equipment->area_id,
                    // 'distance'=>$data['distance'],
                    'task_plan_id'=>$task_plan_task->task_plan_id,
                    'task_mcscr_status_id'=>4,
                    'schedule_by_user_id'=>Auth::user()->id,
                ]);
        
                if( $request->has('subtasks')){
                foreach ( $data['subtasks'] as $item){
        
                    TaskMcscrSubTask::create([
                        'task_mcscr_id'=>$taskmcscr->id,
                        'subtask_id'=>$item['subtask_id'],
                        'answer'=>$item['name'],
                        'task_plan_task_id'=>$data['task_plan_task_id'],
                        'task_plan_id'=>$task_plan_task->task_plan_id,
                    ]);
        
                }
            }
    
                // foreach ( $data['materials'] as $item){
        
                //     TaskMcscrMaterial::create([
    
                //         'task_mcscr_id'=>$taskmcscr->id,
                //         'task_plan_task_material_id'=>$item['id'],
                //         'task_plan_task_id'=>$data['task_plan_task_id'],
                //         'task_plan_id'=>$task_plan_task->task_plan_id,
                //         'quantity'=>$item['quantity'],
    
                //     ]);
    
                    
        
                // }
    
                //CRIAR DE REQUISICAO DE MATERIAL
                if( $request->has('materials')){
    
                $requeststock = RequestStock::create([
                    'task_mcscr_id'=>$taskmcscr->id,
                    'schedule_for'=>$data['schedule_for'],
                    'first_observation'=>'Criado automaticamente pela criação da atividade',
                    'created_by_user_id'=>Auth::user()->id,
                    'request_stock_status_id'=>5
                ]);
    
                foreach ($data['materials'] as $item){
                    RequestStockItem::create([
                        'request_stock_id'=>$requeststock->id,
                        'product_id'=>$item['product_id'],
                        'stock_center_id'=>1,
                        'quantity'=>$item['quantity'],
                        'obs'=>'Criado automaticamente pela criação da atividade'
                    ]);
                }
            }
                //FIM DE CRIACAO DE REQUISICAO
    
                //CRIAR DE REQUISICAO DE TECNICO
                if( $request->has('departments')){
    
                $requesttechnician = RequestTechnician::create([
                    'task_mcscr_id'=>$taskmcscr->id,
                    'schedule_for'=>$data['schedule_for'],
                    'first_observation'=>'Criado automaticamente pela criação da atividade',
                    'created_by_user_id'=>Auth::user()->id,
                    'request_technician_status_id'=>5
                ]);
    
                foreach ($data['departments'] as $item){
                    for ($i=0; $i <$item['quantity'] ; $i++) { 
                        RequestTechnicianItem::create([
                            'request_technician_id'=>$requesttechnician->id,
                            'department_id'=>$item['id'],
                            'obs'=>'Criado automaticamente pela criação da atividade'
                        ]);
                    }
                   
                }
            }
    
                //FIM DE CRIACAO DE TECNICO
    
                // foreach ( $data['departments'] as $item){
        
                //     TaskMcscrDepartment::create([
    
                //         'task_mcscr_id'=>$taskmcscr->id,
                //         'task_plan_task_department_id'=>$item['id'],
                //         'task_plan_task_id'=>$data['task_plan_task_id'],
                //         'task_plan_id'=>$task_plan_task->task_plan_id,
                //         'quantity'=>$item['quantity'],
    
                //     ]);
        
                // }
        
            } catch (\Throwable $th) {
                throw $th;
            }

            $msg = 'Foi programada uma Atividade planeada para o Equipamento '.$taskmcscr->equipment->name.'('.$taskmcscr->equipment->ref.') para o dia '.$data['schedule_for'];
            $user = User::all();
            Notification::send($user,new Operation($msg));


            return [
                'message'=>'Atividade MCSCR Criada com sucesso'
            ];
        // }else{
        //     return response()->json([
        //         'message' => 'Não foi possivel criar a Atividade, porque existe uma atividade MCSCR em execução ou pausado. Termine a execução e volte a criar um novo..',
        //     ], 404);
            
        // }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        

        $taskmcscr = TaskMcscr::
        with('task_mcscr_status')
        ->with('equipment.distance_control')
        ->with('equipment.type_equipment')
        ->with('closed_by_user')
        ->with('opened_by_user')
        ->with('area.province')
        ->with('destination.province')
        ->with('task_plan')
        ->with('task_plan_task')
        ->with('subtasks.subtask.typesubtask')
        ->with('schedule_by_user')
        ->with('materials.task_plan_task_material.product')
        ->with('requeststock.requestitens.product')
        ->with('departments.task_plan_task_department.department')
        ->find($id);

        $requeststock = RequestStock::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.product')
        ->where('task_mcscr_id',$id)->get();

        $requesttechnician = RequestTechnician::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.technician')
        ->with('requestitens.department')
        ->where('task_mcscr_id',$id)->get();

        $requesttool = RequestTool::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.tool')
        ->where('task_mcscr_id',$id)->get();

        $destination = Destination::find($taskmcscr->destination_id);



        $taskmcscrstatuses = TaskMcscrStatus::get();

        return [
            'taskmcscr'=>$taskmcscr,
            'requeststock'=>$requeststock,
            'requesttechnician'=>$requesttechnician,
            'taskmcscrstatuses'=>$taskmcscrstatuses,
            'requesttool'=>$requesttool,
            'destination'=>$destination,
        ];


    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $taskmcscr = TaskMcscr::
        with('task_mcscr_status')
        ->with('equipment.distance_control')
        ->with('closed_by_user')
        ->with('opened_by_user')
        ->with('area')
        ->with('destination')
        ->with('task_plan')
        ->with('task_plan_task')
        ->with('subtasks.subtask')
        ->with('schedule_by_user')
        ->with('materials.task_plan_task_material.product')
        ->with('requeststock.requestitens.product')
        ->with('departments.task_plan_task_department.department')
        ->find($id);

        $requeststock = RequestStock::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.product')
        ->where('task_mcscr_id',$id)->get();

        $requesttechnician = RequestTechnician::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.technician')
        ->with('requestitens.department')
        ->where('task_mcscr_id',$id)->get();

        $requesttool = RequestTool::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.tool')
        ->where('task_mcscr_id',$id)->get();

        if($taskmcscr->task_mcscr_status_id == 4){
            $taskmcscrstatuses = TaskMcscrStatus::where('id',3)->orWhere('id',4)->get();
        }elseif($taskmcscr->task_mcscr_status_id == 3){
            $taskmcscrstatuses = TaskMcscrStatus::where('id',3)->orWhere('id',2)->get();
        }
        

        return [
            'taskmcscr'=>$taskmcscr,
            'requeststock'=>$requeststock,
            'requesttechnician'=>$requesttechnician,
            'taskmcscrstatuses'=>$taskmcscrstatuses,
            'requesttool'=>$requesttool
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();

      
        $taskmcscr = TaskMcscr::find($id);
        $equipment = Equipment::find($taskmcscr->equipment_id);

        if($data['task_mcscr_status_id'] == 3){
            $taskmcscr_test = TaskMcscr::where('equipment_id',$equipment->id)->where('task_mcscr_status_id',3)->get();
            $mcscr_test = Mcscr::where('equipment_id',$equipment->id)->where('mcscr_status_id','!=',1)->get();
            
            if($taskmcscr_test->count() > 0){
                return response()->json([
                    'message' => 'Não foi possível iniciar a Atividade Planeada para este equipamento. Existe uma Atividade Planeada em execução, termine e volte a tentar novamente.',
                ], 404);
            }

            if($mcscr_test->count() > 0){
                return response()->json([
                    'message' => 'Não foi possível iniciar a Atividade Planeada para este equipamento. Existe um MCSCR em execução, termine e volte a tentar novamente.',
                ], 404);
            }
        }

        

        $taskmcscr->update([

            'distance'=>$data['distance'],
            'material_labor'=>$data['material_labor'],
            'material_cost'=>$data['material_cost'],
            'task_mcscr_status_id'=>$data['task_mcscr_status_id'],
            'observation'=>$data['observation'] ?? $taskmcscr->observation,

            'opened_at'=>$data['task_mcscr_status_id'] == 3 ? ($taskmcscr->opened_at == null ? now() : $taskmcscr->opened_at) : $taskmcscr->opened_at,
            'opened_by_user_id'=>$data['task_mcscr_status_id'] == 3 ? Auth::user()->id : $taskmcscr->opened_by_user_id,

            'closed_at'=>$data['task_mcscr_status_id'] == 2 ? ($taskmcscr->closed_at == null ? now() : $taskmcscr->closed_at) : $taskmcscr->closed_at,
            'closed_by_user_id'=>$data['task_mcscr_status_id'] == 2 ? Auth::user()->id :  $taskmcscr->closed_by_user_id,

            'task_mcscr_status_id'=>$data['task_mcscr_status_id'],
            'observation'=>$data['observation'] ?? '',

        ]);

        foreach ( $data['subtasks'] as $item){

            $tasksubtask = TaskMcscrSubTask::find($item['subtask_id']);
            $tasksubtask->update([
                'answer'=>$item['name']
            ]);

        }

        if($taskmcscr->task_mcscr_status_id == 2){

        $taskmcscr2 = TaskMcscr::find($id);

        $opened_time = strtotime($taskmcscr2->opened_at);
        $closed_time = strtotime($taskmcscr2->closed_at);
        $time = $closed_time - $opened_time;

        $total_hours = round($time/3600, 1);

        $taskmcscr2->update([
            'total_hours'=>$total_hours
        ]);

        $equipment->update([
            'equipment_status_id'=>1
        ]);
  
        }

        if($data['task_mcscr_status_id'] == 3){
            

            $equipment->update([
                'equipment_status_id'=>2
            ]);
            $requeststock = RequestStock::where('task_mcscr_id',$taskmcscr->id)->get();
            $requesttechnician = RequestTechnician::where('task_mcscr_id',$taskmcscr->id)->get();

            foreach($requeststock as $stock){
                $stock->update([
                    'request_stock_status_id'=>1
                ]);
            }

            foreach($requesttechnician as $technician){
                $technician->update([
                    'request_technician_status_id'=>1
                ]);
            }
        }

        if($data['task_mcscr_status_id'] == 2){
            $requesttech = RequestTechnician::where('task_mcscr_id',$id)->get();
            $requesttool = RequestTool::where('task_mcscr_id',$id)->get();

            foreach($requesttech as $item){

                foreach($item->requestitens as $item2){
                    $technician = Technician::find($item2->technician_id);
                    if($technician!=null){
                        $technician->update([
                            'status'=>1
                        ]);
                    }
                }
            }

            foreach($requesttool as $item){

                foreach($item->requestitens as $item3){

                    $tool = ToolShop::find($item3->tool_id);
                    if($tool != null){
                        $tool->update([
                            'status'=>1
                        ]);
                    }
                    

                }
            }


        

            $msg = 'O Atividade Planeada para o Equipamento '.$taskmcscr->equipment->name.'('.$taskmcscr->equipment->ref.') foi terminado.O equipamento e os recursos alocados encontram-se disponível .';
            $user = User::all();
            Notification::send($user,new Operation($msg));
        }


        return $taskmcscr;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function download(string $id){

        $taskmcscr = TaskMcscr::
        with('task_mcscr_status')
        ->with('equipment.distance_control')
        ->with('equipment.type_equipment')
        ->with('closed_by_user')
        ->with('opened_by_user')
        ->with('area.province')
        ->with('destination.province')
        ->with('task_plan')
        ->with('task_plan_task')
        ->with('subtasks.subtask.typesubtask')
        ->with('schedule_by_user')
        ->with('materials.task_plan_task_material.product')
        ->with('requeststock.requestitens.product')
        ->with('departments.task_plan_task_department.department')
        ->find($id);

        $requeststock = RequestStock::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.product')
        ->where('task_mcscr_id',$id)->get();

        $requesttechnician = RequestTechnician::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.technician')
        ->with('requestitens.department')
        ->where('task_mcscr_id',$id)->get();

        $requesttool = RequestTool::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.tool')
        ->where('task_mcscr_id',$id)->get();




        $taskmcscrstatuses = TaskMcscrStatus::get();

        $pdf = Pdf::loadView('pdf.taskmcscr', compact('taskmcscr','requeststock','requesttechnician','requesttool','taskmcscrstatuses'))->setOptions([
            'setPaper'=>'a4',
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => 'true'
        ]);
        return $pdf->setPaper('a4')->stream('taskmcscr.pdf');

        

        

        

        
    }

    public function calendar(){

        $tasks = TaskMcscr::where('destination_id',Auth::user()->destination_id)->get()->map(function($task){
            return [
                'id'=>'taskk'.$task->id,
                'title'=>'PREVENTIVA: '.$task->equipment->ref.' ( '.$task->task_plan_task->name.' ).',
                'date'=>$task->schedule_for,
                'start'=>$task->schedule_for,
                'backgroundColor'=>'#50B3C7',
                'borderColor'=>'#50B3C7',
                'color'=>'#50B3C7'
            ];
        });

        $mcscr =  Mcscr::where('destination_id',Auth::user()->destination_id)->get()->map(function($mcscr){
            return [
                'id'=>'mcscr'.$mcscr->id,
                'title'=>'CORRETIVA: '.$mcscr->equipment->ref.' ( '.$mcscr->reason.' ).',
                'date'=>$mcscr->opened_at,
                'start'=>$mcscr->opened_at,
                'backgroundColor'=>'#D12A06',
                'borderColor'=>'#D12A06',
                'color'=>'#D12A06'
            ];
        });

        $novoarray = array_merge($mcscr->toArray(),$tasks->toArray());


        return $novoarray;
    }

    public function detailcalendar($parms){

        $string = substr($parms, 5);

        if($parms[0] == 't'){
            $event = TaskMcscr::
                with('task_mcscr_status')
                ->with('equipment.distance_control')
                ->with('equipment.type_equipment')
                ->with('closed_by_user')
                ->with('opened_by_user')
                ->with('area.province')
                ->with('destination.province')
                ->with('task_plan')
                ->with('task_plan_task')
                ->with('subtasks.subtask.typesubtask')
                ->with('schedule_by_user')
                ->with('materials.task_plan_task_material.product')
                ->with('requeststock.requestitens.product')
                ->with('departments.task_plan_task_department.department')
                ->find($string);

                return[
                    'taskmcscr'=>$event
                ];
        }
        if($parms[0] == 'm'){
            $event = Mcscr::
            with('mcscr_status')
            ->with('equipment.type_equipment')
            ->with('type_malfunction')
            ->with('reason_name')
            ->with('component')
            ->with('subcomponent')
            ->with('cause_name')
            ->with('solution_name')
            ->with('consequence_name')
            ->with('recommendation_name')
            ->with('consequence_name')
            ->with('opened_by_user')
            ->with('closed_by_user')
            ->with('area.province')
            ->with('destination.province')
            ->with('waiting_status')
            ->find($string);

            return[
                'mcscr'=>$event
            ];
        }



        
    }
}
