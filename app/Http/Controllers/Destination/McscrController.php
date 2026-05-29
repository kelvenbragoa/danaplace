<?php

namespace App\Http\Controllers\Destination;

use App\Http\Controllers\Controller;
use App\Models\Cause;
use App\Models\Consequence;
use App\Models\Department;
use App\Models\Destination;
use App\Models\Equipment;
use App\Models\EquipmentComponent;
use App\Models\EquipmentStatus;
use App\Models\EquipmentSubComponent;
use App\Models\Mcscr;
use App\Models\McscrStatus;
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

class McscrController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');
        $status = request('status');

            $mcscrs = Mcscr::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('reason','like',"%{$searchQuery}%")->orWhere('cause','like',"%{$searchQuery}%")->orWhere('consequence','like',"%{$searchQuery}%")->orWhere('solution','like',"%{$searchQuery}%")->orWhere('recommendation','like',"%{$searchQuery}%");
            })
            ->when(request('status'),function($query,$status){
                $query->where('mcscr_status_id',$status);
            })
            ->with('mcscr_status')
            ->with('equipment')
            ->with('reason_name')
            ->with('solution_name')
            ->with('consequence_name')
            ->with('recommendation_name')
            ->with('consequence_name')
            // ->orderBy('opened_at','desc')
            ->where('destination_id',Auth::user()->destination_id)
            ->orderBy('opened_at','desc')
            ->paginate();

            return [
                'mcscr'=>$mcscrs,
                'total' => Mcscr::where('destination_id',Auth::user()->destination_id)->count(),
                'terminado' => Mcscr::where('mcscr_status_id',1)->where('destination_id',Auth::user()->destination_id)->count(),
                'pendente' => Mcscr::where('mcscr_status_id',4)->where('destination_id',Auth::user()->destination_id)->count(),
                'aprovacao' => Mcscr::where('mcscr_status_id',2)->where('destination_id',Auth::user()->destination_id)->count(),
                'diagnostico' => Mcscr::where('mcscr_status_id',5)->where('destination_id',Auth::user()->destination_id)->count(),
                'programado' => Mcscr::where('mcscr_status_id',6)->where('destination_id',Auth::user()->destination_id)->count(),
                'execucao' => Mcscr::where('mcscr_status_id',3)->where('destination_id',Auth::user()->destination_id)->count(),
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




        $mcscr = Mcscr::create([
            'reason'=>$data['reason'],
            'reason_id'=>$data['reason_id'] == 0 ? 0 : $data['reason_id'],
            
            'cause_id'=>0,
            'solution_id'=>0,
            'consequence_id'=>0,
            'recommendation_id'=>0,

            'opened_at'=>now(),
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
            'mcscr_status_id'=>4,
            'distance'=>$data['distance'],
            'waiting_status_id'=>4,
            'first_observation'=> $data['first_observation']
        ]);



        $msg = 'Foi aberto um novo MCSCR para o Equipamento '.$equipment->name.'('.$equipment->ref.').O equipamento encontra-se indisponível.';
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



        return [
            'mcscr'=>$mcscr,
            'requesttechnician'=>$requesttechnician,
             'requeststock'=>$requeststock,
             'requesttool'=>$requesttool,
             'destination'=>$destination


        
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
        $products = Product::with('unity')->orderBy('name','asc')->get();
        $departments = Department::orderBy('name','asc')->get();
        $tools = ToolShop::where('status',1)->orderBy('name','asc')->get();

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
        }
       
      
        

        


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
             'sub'

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
       
        //fim criacao de request stock

        //criacao de request technician

        if($request->has('departments')){
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

        

        //fim criacao de request technician

        //criacao de adicao de ferramentaria
        if($request->has('tools')){
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

        //fim de ferramentaria

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
            'diagnosis_start_at'=> $data['mcscr_status_id'] == 5 ? ($mcscr->diagnosis_start_at == null ? now() : $mcscr->diagnosis_start_at) : $mcscr->diagnosis_start_at,
            'diagnosis_end_at'=> $data['mcscr_status_id'] == 3 ?  ($mcscr->diagnosis_end_at == null ? now() : $mcscr->diagnosis_end_at) : $mcscr->diagnosis_end_at,

            'execution_start_at'=> $data['mcscr_status_id'] == 3 ? ($mcscr->execution_start_at == null ? now() : $mcscr->execution_start_at) : $mcscr->execution_start_at,
            'execution_end_at'=> $data['mcscr_status_id'] == 2 ? ($mcscr->execution_end_at == null ? now() : $mcscr->execution_end_at) : $mcscr->execution_end_at,

            'awaiting_approval_start_at'=>$data['mcscr_status_id'] == 2 ? ($mcscr->awaiting_approval_start_at == null ? now() : $mcscr->awaiting_approval_start_at) : $mcscr->awaiting_approval_start_at,
            'awaiting_approval_end_at'=>$data['mcscr_status_id'] == 1 ? ($mcscr->awaiting_approval_end_at == null ? now() : $mcscr->awaiting_approval_end_at) : $mcscr->awaiting_approval_end_at,

            

            'closed_by_user_id'=>$data['mcscr_status_id'] == 1 ? Auth::user()->id : null,
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

            $mcscr2->update([
                'total_hours'=>$total_hours,
                'waiting_status_id'=>4
            ]);



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
}
