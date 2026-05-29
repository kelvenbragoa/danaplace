<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cause;
use App\Models\Consequence;
use App\Models\Department;
use App\Models\Destination;
use App\Models\Equipment;
use App\Models\EquipmentComponent;
use App\Models\EquipmentStatus;
use App\Models\EquipmentSubComponent;
use App\Models\JobCardRecommendationTask;
use App\Models\Mcscr;
use App\Models\McscrResolution;
use App\Models\McscrStatus;
use App\Models\McscrUpload;
use App\Models\Product;
use App\Models\Reason;
use App\Models\Recommendation;
use App\Models\RequestStock;
use App\Models\RequestStockItem;
use App\Models\RequestTechnician;
use App\Models\RequestTechnicianItem;
use App\Models\RequestTool;
use App\Models\RequestToolItem;
use App\Models\Solution;
use App\Models\TaskMcscr;
use App\Models\Technician;
use App\Models\ToolShop;
use App\Models\TypeMalfunction;
use App\Models\User;
use App\Models\WaitingStatus;
use App\Notifications\Operation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class MCSCRController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            ->where('is_generated_by_task',0)
            // ->orderBy('opened_at','desc')
            ->orderBy('opened_at','desc')
            ->paginate(100);

            return [
                'mcscr'=>$mcscrs,
                'total' => Mcscr::where('is_generated_by_task',0)->count(),
                'terminado' => Mcscr::where('mcscr_status_id',1)->where('is_generated_by_task',0)->count(),
                'pendente' => Mcscr::where('mcscr_status_id',4)->where('is_generated_by_task',0)->count(),
                'programado' => Mcscr::where('mcscr_status_id',6)->where('is_generated_by_task',0)->count(),
                'aprovacao' => Mcscr::where('mcscr_status_id',2)->where('is_generated_by_task',0)->count(),
                'diagnostico' => Mcscr::where('mcscr_status_id',5)->where('is_generated_by_task',0)->count(),
                'execucao' => Mcscr::where('mcscr_status_id',3)->where('is_generated_by_task',0)->count(),
                'destinations'=>Destination::orderBy('name')->get()
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
        $taskmcscr_test = TaskMcscr::where('equipment_id',$data['equipment_id'])->where('task_mcscr_status_id',3)->get();
        $mcscr_test = Mcscr::where('equipment_id',$data['equipment_id'])->where('mcscr_status_id','!=',1)->get();

      

    //    if($data['mcscr_status_id'] == 4){
    //     if($mcscr_test->count() > 0){
    //         return response()->json([
    //             'message' => 'Não foi possível adicionar o MCSCR para este equipamento. Existe um MCSCR em execução, termine e volte a tentar novamente.',
    //         ], 404);
    //     }

    //     if($taskmcscr_test->count() > 0){
    //         return response()->json([
    //             'message' => 'Não foi possível adicionar o MCSCR para este equipamento. Existe uma Atividade Planeada em execução, termine e volte a tentar novamente.',
    //         ], 404);
    //     }
    //    }
        

       

        $request->validate([
            'reason' =>'required',
            'reason_id' =>'required',
            'opened_at' =>'required',
            'output_forecast' =>'required',
            'type_equipment_id' =>'required',
            'equipment_id' =>'required',
            'equipment_component_id' =>'required',
            'type_malfunction_id' =>'required',
            'task_id' =>'required',
            'distance' =>'required',    
        ]);

        $equipment = Equipment::find($data['equipment_id']);

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
            'reason'=>$data['reason'],
            'reason_id'=>$data['reason_id'] == 0 ? 0 : $data['reason_id'],

            'cause_id'=>0,
            'solution_id'=>0,
            'consequence_id'=>0,
            'recommendation_id'=>0,


            'opened_at'=>$data['opened_at'],
            'is_rework'=>0,
            'output_forecast'=>$data['output_forecast'],
            'type_equipment_id'=>$equipment->type_equipment_id,
            'equipment_id'=>$data['equipment_id'],
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
            'first_observation'=> $data['first_observation']
        ]);

        if($request->has('mcscrs')){
            foreach ($data['mcscrs'] as $item){
                    McscrResolution::create([
                        'mcscr_id'=>$mcscr->id,
                        'resolution_name'=>$item['resolution_name'],
                    ]);
            }
        }



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
        $mcscr = Mcscr::
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
        ->find($id);
        
        $requeststock = RequestStock::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.product')
        ->where('mcscr_id',$id)->get();

        $requesttechnician = RequestTechnician::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.technician')
        ->with('requestitens.department')
        ->where('mcscr_id',$id)->get();

        $requesttool = RequestTool::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.tool')
        ->where('mcscr_id',$id)->get();
        $destination = Destination::find($mcscr->destination_id);

        $upload = McscrUpload::where('mcscr_id',$id)->get();

        foreach ($upload as $item){
            $item->file = Storage::disk('s3')->temporaryUrl(
                $item->file,
                now()->addMinutes(10),
                ['ResponseContentDisposition' => 'attachment']
            );
        }

        $openedby = User::find($mcscr->opened_by_user_id);
        
        $openedby->signature =  Storage::disk('s3')->temporaryUrl(
            $openedby->signature,
            now()->addMinutes(10),
            ['ResponseContentDisposition' => 'attachment']
        );

        // $closedby = User::find($mcscr->closed_by_user_id);
        // if($closedby!= null){
        //     $closedby->signature =  Storage::disk('s3')->temporaryUrl(
        //         $closedby->signature,
        //         now()->addMinutes(10),
        //         ['ResponseContentDisposition' => 'attachment']
        //     );
        // }
        
        
        $destinationuser = User::where('role_id',8)->where('destination_id',$mcscr->destination_id)->first();

        if($destinationuser!= null){
            if($destinationuser->signature != null){
                $destinationuser->signature =  Storage::disk('s3')->temporaryUrl(
                    $destinationuser->signature,
                    now()->addMinutes(10),
                    ['ResponseContentDisposition' => 'attachment']
                );
            }
            
        }

        $resolutions = McscrResolution::where('mcscr_id',$mcscr->id)->whereNot('mcscr_status_id',null)->with('mcscr_status')->orderBy('id','asc')->get();

        $preresolutions = McscrResolution::where('mcscr_id',$mcscr->id)->where('mcscr_status_id',null)->orderBy('id','asc')->get();

        $jobtasks = JobCardRecommendationTask::
        with('type_equipment')
        ->with('equipment')
        ->with('status')
        ->with('area')
        ->with('destination')
        ->where('mcscr_id',$mcscr->id)
        ->get();

        return [
            'mcscr'=>$mcscr,
            'requesttechnician'=>$requesttechnician,
             'requeststock'=>$requeststock,
             'requesttool'=>$requesttool,
             'destination'=>$destination,
             'uploads'=>$upload,
             'openedby'=>$openedby,
            //  'closedby'=>$closedby,

             'destinationuser'=>$destinationuser,
             'resolutions'=>$resolutions,
             'preresolutions'=>$preresolutions,
             'jobtasks'=>$jobtasks

        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $mcscr = Mcscr::
        with('opened_by_user')
        ->with('equipment')
        ->find($id);

        $reasons = Reason::orderBy('name','asc')->get();
        $solutions = Solution::orderBy('name','asc')->get();
        $consequences = Consequence::orderBy('name','asc')->get();
        $recommendations = Recommendation::orderBy('name','asc')->get();
        $causes = Cause::orderBy('name','asc')->get();
        $waiting = WaitingStatus::orderBy('id','desc')->get();
        $availabilities = EquipmentStatus::orderBy('id','asc')->get();
        
        $malfunctions = TypeMalfunction::orderBy('name','asc')->get();

        $components = EquipmentComponent::where('equipment_id',$mcscr->equipment_id)->get();
        $sub_components = EquipmentSubComponent::where('equipment_id',$mcscr->equipment_id)->where('equipment_component_id',$mcscr->equipment_component_id)->get();
        $products = Product::with('unity')->orderBy('name','asc')->limit(10)->get();
        $departments = Department::orderBy('name','asc')->get();
        $tools = ToolShop::where('status',1)->orderBy('name','asc')->get();
        $resolutions = McscrResolution::where('mcscr_id',$mcscr->id)->whereNot('mcscr_status_id',null)->with('mcscr_status')->orderBy('id','asc')->get();
        $preresolutions = McscrResolution::where('mcscr_id',$mcscr->id)->where('mcscr_status_id',null)->orderBy('id','asc')->get();


        $requeststock = RequestStock::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.product')
        ->where('mcscr_id',$id)->get();

        $requesttechnician = RequestTechnician::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.technician')
        ->with('requestitens.department')
        ->where('mcscr_id',$id)->get();

        $requesttool = RequestTool::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.tool')
        ->where('mcscr_id',$id)->get();

        if($mcscr->mcscr_status_id == 4){
            $mcscrstatuses = McscrStatus::where('id',5)->orWhere('id',4)->get();
        }elseif($mcscr->mcscr_status_id == 5){
            $mcscrstatuses = McscrStatus::where('id',5)->orWhere('id',3)->get();
        }elseif($mcscr->mcscr_status_id == 3){
            $mcscrstatuses = McscrStatus::where('id',2)->orWhere('id',3)->get();
        }elseif($mcscr->mcscr_status_id == 2){
            $mcscrstatuses = McscrStatus::where('id',2)->orWhere('id',1)->get();
        }elseif($mcscr->mcscr_status_id == 6){
            $mcscrstatuses = McscrStatus::where('id',4)->orWhere('id',6)->get();
        }elseif($mcscr->mcscr_status_id == 1){
            $mcscrstatuses = McscrStatus::where('id',1)->get();
        }

        $users = User::where('role_id',4)->orWhere('role_id',3)->orWhere('role_id',1)->orderBy('firstName','asc')->get();


        return[
             'mcscr'=>$mcscr,
             'reasons'=>$reasons,
             'consequences'=>$consequences,
             'causes'=>$causes,
             'solutions'=>$solutions,
             'recommendations'=>$recommendations,
             'waiting' =>$waiting,
             'availabilities'=>$availabilities,
             'mcscrstatuses'=>$mcscrstatuses,
             'malfunctions'=> $malfunctions,
             'components'=>$components,
             'sub_components'=>$sub_components,
             'products'=>$products,
             'departments'=>$departments,
             'requesttechnician'=>$requesttechnician,
             'requeststock'=>$requeststock,
             'requesttool'=>$requesttool,
             'tools'=>$tools,
             'resolutions'=>$resolutions,
             'preresolutions'=>$preresolutions,
             'users'=>$users
            ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();

       

        $request->validate([
            'reason' =>'required',
            'reason_id' =>'required',
            'cause' =>'required',
            'cause_id' =>'required',
            'solution' =>'required',
            'solution_id' =>'required',
            'consequence' =>'required',
            'consequence_id' =>'required',
            'recommendation' =>'required',
            'recommendation_id' =>'required',
            // 'opened_at' =>'required',
            'closed_at' =>'required',
            'output_forecast' =>'required',
            'waiting_status_id' =>'required',
            'equipment_component_id' =>'required',
            'equipment_sub_component_id' =>'required',
            'type_malfunction_id' =>'required',
            'equipment_status_id' =>'required',
            'mcscr_status_id' =>'required',
            'opened_by_user_id' =>'required',
            'first_observation' =>'required',  
            'material_labor' =>'required|numeric',  
            'material_cost' =>'required|numeric',            
        ]);


        $mcscr = Mcscr::find($id);

        $equipment = Equipment::find($mcscr->equipment_id);

        if($data['mcscr_status_id'] == 4){
            $taskmcscr_test = TaskMcscr::where('equipment_id',$equipment->id)->where('task_mcscr_status_id',3)->get();
            $mcscr_test = Mcscr::where('equipment_id',$equipment->id)->where('mcscr_status_id',3)->get();

      

        if($mcscr_test->count() > 0){
            return response()->json([
                'message' => 'Não foi possível alterar o estado do MCSCR para este equipamento. Existe um MCSCR em execução, termine e volte a tentar novamente.',
            ], 404);
        }

        if($taskmcscr_test->count() > 0){
            return response()->json([
                'message' => 'Não foi possível alterar o do MCSCR para este equipamento. Existe uma Atividade Planeada em execução, termine e volte a tentar novamente.',
            ], 404);
        }
       
            $equipment->update([
                'equipment_status_id'=>2
            ]);
        }

        if($data['mcscr_status_id'] == 1){

            $equipment->update([
                'equipment_status_id'=>1
            ]);

            $components = EquipmentComponent::where('equipment_id',$mcscr->equipment_id)->get();

            foreach($components as $item){
                $item->update([
                    'equipment_status_id'=>1
                ]);
            }

            $subcomponents = EquipmentSubComponent::where('equipment_id',$mcscr->equipment_id)->get();

            foreach($subcomponents as $item){
                $item->update([
                    'equipment_status_id'=>1
                ]);
            }
        }

        //criacao de request stock
        if( $request->has('materials')){
            if(count($data['materials']) > 0){
                $requeststock = RequestStock::create([
                    'mcscr_id'=>$mcscr->id,
                    'first_observation'=>'Criado automaticamente pela criação da atividade',
                    'created_by_user_id'=>Auth::user()->id,
                    'request_stock_status_id'=>1
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
        }
       
        //fim criacao de request stock

        //criacao de request technician

        if($request->has('departments')){
            if(count($data['departments']) > 0){
                $requesttechnician = RequestTechnician::create([
                    'mcscr_id'=>$mcscr->id,
                    'first_observation'=>'Criado automaticamente pela criação da atividade',
                    'created_by_user_id'=>Auth::user()->id,
                    'request_technician_status_id'=>1
                ]);
        
                foreach ($data['departments'] as $item){
                    for ($i=0; $i <$item['quantity'] ; $i++) { 
                        RequestTechnicianItem::create([
                            'request_technician_id'=>$requesttechnician->id,
                            'department_id'=>$item['department_id'],
                            'obs'=>'Criado automaticamente pela criação da atividade'
                        ]);
                    }
                
                }
            }
        }

        

        //fim criacao de request technician

        //criacao de adicao de ferramentaria
        if($request->has('tools')){
            if(count($data['tools']) > 0){

            $requesttool = RequestTool::create([
                'mcscr_id'=>$mcscr->id,
                'first_observation'=>'Criado automaticamente pela criação da atividade',
                'created_by_user_id'=>Auth::user()->id,
                'request_tool_status_id'=>1
            ]);

            foreach ($data['tools'] as $item){

                $test_if_exists = RequestToolItem::where('request_tool_id',$requesttool->id)->where('tool_id',$item['tool_id'])->first();

                if($test_if_exists==null){
                
                    RequestToolItem::create([
                        'request_tool_id'=>$requesttool->id,
                        'tool_id'=>$item['tool_id'],
                        'obs'=>'Criado automaticamente pela criação da atividade'
                    ]);
                }
                
            }
        }
        }

        //fim de ferramentaria

        //criacao de adicao accoes de resolucao de avaria
        if($request->has('mcscrs')){
            foreach ($data['mcscrs'] as $item){
                    McscrResolution::create([
                        'mcscr_id'=>$mcscr->id,
                        'resolution_name'=>$item['resolution_name'],
                        'mcscr_status_id'=>$data['mcscr_status_id']
                    ]);
            }
        }

        //fim de resolucao de avaria

        // if($data['mcscr_status_id'] == 4){
        //     $mcscrstatus = 4;
        // }elseif($data['mcscr_status_id'] == 5){
        //     $mcscrstatus = 4;
        // }

        $mcscr->update([
            'reason'=>$data['reason'],
            'reason_id'=>$data['reason_id'] == 0 ? null : $data['reason_id'],
            'cause'=>$data['cause'],
            'cause_id'=>$data['cause_id'] == 0 ? null : $data['cause_id'],
            'solution'=>$data['solution'],
            'solution_id'=>$data['solution_id'] == 0 ? null : $data['solution_id'],
            'consequence'=>$data['consequence'],
            'consequence_id'=>$data['consequence_id'] == 0 ? null : $data['consequence_id'],
            'recommendation'=>$data['recommendation'],
            'recommendation_id'=>$data['recommendation_id'] == 0 ? null : $data['recommendation_id'],
            // 'opened_at'=>$data['opened_at'],
            'closed_at'=>$data['closed_at'],
            // 'closed_at'=>$data['mcscr_status_id'] == 1 ? now() : null,
            'output_forecast'=>$data['output_forecast'],
            'equipment_component_id'=>$data['equipment_component_id'] == 0 ? null : $data['equipment_component_id'],
            'equipment_sub_component_id'=>$data['equipment_sub_component_id'] == 0 ? null : $data['equipment_sub_component_id'],
            'type_malfunction_id'=>$data['type_malfunction_id'],
            // 'opened_by_user_id'=>$data['opened_by_user_id'],
            // 'closed_by_user_id'=>Auth::user()->id,
            'mcscr_status_id'=>$data['mcscr_status_id'],
            'waiting_status_id'=>$data['waiting_status_id'],
            'first_observation'=> $data['first_observation'],
            'material_labor'=> $data['material_labor'],
            'material_cost'=> $data['material_cost'],
            'is_rework'=>$data['is_rework'],
            'distance'=>$data['distance'],
            // 'diagnosis_start_at'=> $data['mcscr_status_id'] == 5 ? ($mcscr->diagnosis_start_at == null ? now() : $mcscr->diagnosis_start_at) : $mcscr->diagnosis_start_at,
            // 'diagnosis_end_at'=> $data['mcscr_status_id'] == 3 ?  ($mcscr->diagnosis_end_at == null ? now() : $mcscr->diagnosis_end_at) : $mcscr->diagnosis_end_at,

            // 'execution_start_at'=> $data['mcscr_status_id'] == 3 ? ($mcscr->execution_start_at == null ? now() : $mcscr->execution_start_at) : $mcscr->execution_start_at,
            // 'execution_end_at'=> $data['mcscr_status_id'] == 2 ? ($mcscr->execution_end_at == null ? now() : $mcscr->execution_end_at) : $mcscr->execution_end_at,

            // 'awaiting_approval_start_at'=>$data['mcscr_status_id'] == 2 ? ($mcscr->awaiting_approval_start_at == null ? now() : $mcscr->awaiting_approval_start_at) : $mcscr->awaiting_approval_start_at,
            // 'awaiting_approval_end_at'=>$data['mcscr_status_id'] == 1 ? ($mcscr->awaiting_approval_end_at == null ? now() : $mcscr->awaiting_approval_end_at) : $mcscr->awaiting_approval_end_at,

            'diagnosis_start_at'=> $data['mcscr_status_id'] == 5 ? ($mcscr->diagnosis_start_at == null ? $data['closed_at'] : $mcscr->diagnosis_start_at) : $mcscr->diagnosis_start_at,
            'diagnosis_end_at'=> $data['mcscr_status_id'] == 3 ?  ($mcscr->diagnosis_end_at == null ? $data['closed_at'] : $mcscr->diagnosis_end_at) : $mcscr->diagnosis_end_at,

            'execution_start_at'=> $data['mcscr_status_id'] == 3 ? ($mcscr->execution_start_at == null ? $data['closed_at'] : $mcscr->execution_start_at) : $mcscr->execution_start_at,
            'execution_end_at'=> $data['mcscr_status_id'] == 2 ? ($mcscr->execution_end_at == null ? $data['closed_at'] : $mcscr->execution_end_at) : $mcscr->execution_end_at,

            'awaiting_approval_start_at'=>$data['mcscr_status_id'] == 2 ? ($mcscr->awaiting_approval_start_at == null ? $data['closed_at'] : $mcscr->awaiting_approval_start_at) : $mcscr->awaiting_approval_start_at,
            'awaiting_approval_end_at'=>$data['mcscr_status_id'] == 1 ? ($mcscr->awaiting_approval_end_at == null ? $data['closed_at'] : $mcscr->awaiting_approval_end_at) : $mcscr->awaiting_approval_end_at,

            'closed_by_user_id'=>$data['mcscr_status_id'] == 1 ? (isset($data['user_id']) ? $data['user_id'] : Auth::user()->id) : $mcscr->closed_by_user_id,
            'approval_by_user_id'=>$data['mcscr_status_id'] == 1 ? (isset($data['user_id']) ? $data['user_id'] : Auth::user()->id) : $mcscr->approval_by_user_id,
            'scheduled_by_user_id'=>$data['mcscr_status_id'] == 6 ? (isset($data['user_id']) ? $data['user_id'] : Auth::user()->id) : $mcscr->scheduled_by_user_id,
            'diagnosis_by_user_id'=>$data['mcscr_status_id'] == 5 ? (isset($data['user_id']) ? $data['user_id'] : Auth::user()->id) : $mcscr->diagnosis_by_user_id,
            'execution_by_user_id'=>$data['mcscr_status_id'] == 3 ? (isset($data['user_id']) ? $data['user_id'] : Auth::user()->id) : $mcscr->execution_by_user_id,

            'trip_start_date'=> isset($data['trip_start_date']) ? $data['trip_start_date'] : $mcscr->trip_start_date,
            'trip_return_date'=>isset($data['trip_return_date']) ? $data['trip_return_date'] : $mcscr->trip_return_date,
            'trip_travel_hours'=>isset($data['trip_travel_hours']) ? $data['trip_travel_hours'] : $mcscr->trip_travel_hours,
            'trip_travel_of'=>isset($data['trip_travel_of']) ? $data['trip_travel_of'] : $mcscr->trip_travel_of,
            'trip_travel_to'=>isset($data['trip_travel_to']) ? $data['trip_travel_to'] : $mcscr->trip_travel_to,
            'trip_distance_traveled'=>isset($data['trip_distance_traveled']) ? $data['trip_distance_traveled'] : $mcscr->trip_distance_traveled,

            'work_start_time'=>isset($data['work_start_time']) ? $data['work_start_time'] : $mcscr->work_start_time,
            'work_return_time'=>isset($data['work_return_time']) ? $data['work_return_time'] : $mcscr->work_return_time,
            'work_total_amount_of_hours'=>isset($data['work_total_amount_of_hours']) ? $data['work_total_amount_of_hours'] : $mcscr->work_total_amount_of_hours,
            'work_nights_at_hotel'=>isset($data['work_nights_at_hotel']) ? $data['work_nights_at_hotel'] : $mcscr->work_nights_at_hotel,
            'work_extra_start_times'=>isset($data['work_extra_start_times']) ? $data['work_extra_start_times'] : $mcscr->work_extra_start_times,
            'work_extra_ending_times'=>isset($data['work_extra_ending_times']) ? $data['work_extra_ending_times'] : $mcscr->work_extra_ending_times,

            

        ]);

        if($data['mcscr_status_id'] == 1){


            $mcscr2 = Mcscr::find($id);
            $requesttech = RequestTechnician::where('mcscr_id',$id)->get();
            $requesttool = RequestTool::where('mcscr_id',$id)->get();

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


            $msg = 'O MCSCR para o Equipamento '.$equipment->name.'('.$equipment->ref.') foi terminado.O equipamento e os recursos alocados encontram-se disponível .';
            $user = User::all();
            Notification::send($user,new Operation($msg));


            $opened_time = strtotime($mcscr2->opened_at);
            $closed_time = strtotime($mcscr2->closed_at);
            $time = $closed_time - $opened_time;

            $total_hours = round($time/3600, 1);

            $jobcardrecomendation = JobCardRecommendationTask::where('mcscr_id',$mcscr->id)->get();

            foreach($jobcardrecomendation as $item){
                $item->update([
                   'status_id'=>1
                ]);
            }
            // if($jobcardrecomendation){
            //     $jobcardrecomendation->update([
            //         'status_id'=>1
            //     ]);
            // }

            $mcscr2->update([
                'total_hours'=>$total_hours,
                'waiting_status_id'=>4
            ]);

            // JobCardRecommendationTask::create([
            //     'mcscr_id'=> $mcscr2->id,
            //     'status_id'=> 2,
            //     'destination_id'=> $mcscr2->destination_id,
            //     'area_id'=> $mcscr2->area_id,
            //     'equipment_id'=> $mcscr2->equipment_id,
            //     'type_equipment_id'=> $mcscr2->type_equipment_id,
            //     'task'=> $mcscr2->recommendation
            // ]);

            

        }

        return $mcscr;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $mcscr = Mcscr::find($id);

        $mcscr->delete();

        return true;
    }

    public function download(string $id){

        $mcscr = Mcscr::
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
        ->find($id);
        
        $requeststock = RequestStock::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.product')
        ->where('mcscr_id',$id)->get();

        $requesttechnician = RequestTechnician::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.technician')
        ->with('requestitens.department')
        ->where('mcscr_id',$id)->get();

        $requesttool = RequestTool::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->with('requestitens.tool')
        ->where('mcscr_id',$id)->get();

        $pdf = Pdf::loadView('pdf.mcscr', compact('mcscr','requeststock','requesttechnician','requesttool'))->setOptions([
            'setPaper'=>'a4',
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => 'true'
        ]);
        return $pdf->setPaper('a4')->stream('mcscr.pdf');

        

        // return [
        //     'mcscr'=>$mcscr,
        //     'requesttechnician'=>$requesttechnician,
        //      'requeststock'=>$requeststock,
        //      'requesttool'=>$requesttool


        
        // ];
    }

    public function viewupload($id){

        $mcscr = Mcscr::
        with('opened_by_user')
        ->with('equipment')
        ->find($id);

        $upload = McscrUpload::where('mcscr_id',$id)->get();

        foreach ($upload as $item){
            $item->file = Storage::disk('s3')->temporaryUrl(
                $item->file,
                now()->addMinutes(10),
                ['ResponseContentDisposition' => 'attachment']
            );
        }

        return response()->json([
            'mcscr'=>$mcscr,
            'uploads'=>$upload
        ],200);


    }

    public function upload(Request $request){


        $data = $request->all();
        $mcscr = Mcscr::
        with('opened_by_user')
        ->with('equipment')
        ->find($data['mcscr_id']);
        $allowedfileExtension=['pdf'];
        $files = $request->file('image');
        if($request->has('image')){

            
            // foreach($files as $file){
                $filename = $files->getClientOriginalName();
                $extension = $files->getClientOriginalExtension();
                $imagePath = $files->store('mcscr-attachment','s3');
                 McscrUpload::create([
                        'file' => $imagePath,
                        'mcscr_id' => $data['mcscr_id'],
                    ]);
            }
        // }

        $upload = McscrUpload::where('mcscr_id',$data['mcscr_id'])->get();

        foreach ($upload as $item){
            $item->file = Storage::disk('s3')->temporaryUrl(
                $item->file,
                now()->addMinutes(10),
                ['ResponseContentDisposition' => 'attachment']
            );
        }


        return response()->json([
            'mcscr'=>$mcscr,
            'uploads'=>$upload
        ],200);


    }

    public function deleteupload($id){
        $mcscr = McscrUpload::find($id);

        $mcscr->delete();

        return true;
    }

    public function deleteresolutions($id){
        $mcscrresolution = McscrResolution::find($id);
        $mcscrresolution->delete();
        return true;
    }
}
