<?php

namespace App\Http\Controllers\Destination;

use App\Http\Controllers\Controller;
use App\Models\Acquisition;
use App\Models\Area;
use App\Models\CenterCost;
use App\Models\CenterCostAccount;
use App\Models\Coin;
use App\Models\Criticaly;
use App\Models\Destination;
use App\Models\DistanceControl;
use App\Models\Equipment;
use App\Models\EquipmentComponent;
use App\Models\EquipmentStatus;
use App\Models\EquipmentSubComponent;
use App\Models\Fuel;
use App\Models\HoursDistanceEquipment;
use App\Models\LoadUnity;
use App\Models\Mcscr;
use App\Models\Supplier;
use App\Models\TaskMcscr;
use App\Models\TypeEquipment;
use App\Models\TypeEquipmentComponent;
use App\Models\TypeEquipmentSubComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

        $equipments = Equipment::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%");
        })
        ->with('destination')
        ->with('area')
        ->with('supplier')
        ->with('type_equipment')
        ->with('equipment_status')
        ->with('criticaly')
        ->with('acquisition')
        ->with('center_cost')
        ->with('distance_control')
        ->with('center_cost_account')
        ->where('destination_id',Auth::user()->destination_id)
        ->orderBy('id','asc')
        ->paginate();

        
        return $equipments;
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
        
        $equipment = Equipment::create($data);


        $components = TypeEquipmentComponent::where('type_equipment_id',$data['type_equipment_id'])->get();

        foreach ($components as $item){

            $equipment_component = EquipmentComponent::create([
                'name'=>$item->name,
                'criticaly_id'=>$item->criticaly_id,
                'equipment_id'=>$equipment->id,
                'equipment_status_id'=>$data['equipment_status_id'],
                'percentage_weigth'=>$item->percentage_weigth,
                'type_equipment_component_id'=>$item->id,
                'model'=>$item->model,
                'make'=>$item->make,
                
            ]);

            $subcomponents = TypeEquipmentSubComponent::where('type_equipment_component_id',$item->id)->get();

            foreach($subcomponents as $item2){
                EquipmentSubComponent::create([
                    'name'=>$item2->name,
                    'criticaly_id'=>$item2->criticaly_id,
                    'equipment_component_id'=>$equipment_component->id,
                    'equipment_id'=>$equipment->id,
                    'equipment_status_id'=>$data['equipment_status_id'],
                    'percentage_weigth'=>$item2->percentage_weigth,
                    'type_equipment_sub_component_id'=>$item2->id,
                    'model'=>$item2->model,
                    'make'=>$item2->make,
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
        $equipment = Equipment::with('destination')
        ->with('area')
        ->with('supplier')
        ->with('type_equipment')
        ->with('equipment_status')
        ->with('criticaly')
        ->with('acquisition')
        ->with('center_cost')
        ->with('distance_control')
        ->with('center_cost_account')
        ->with('coin')
        ->with('lastdistance')
        ->with('load_unity')
        ->find($id);
        $criticals = Criticaly::orderBy('name','asc')->get();
        $equipmentstatuses = EquipmentStatus::orderBy('name','asc')->get();

        $distance_control = DistanceControl::get();

        $searchQuery = request('query');

        $components = EquipmentComponent::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%");
        })
        ->with('equipmentstatus')
        ->with('subcomponents')
        ->with('criticality')
        ->where('equipment_id',$id)
        ->orderBy('name','asc')
        ->paginate();

        $mcscrData = Mcscr::where('equipment_id',$id)
        ->with('mcscr_status')
            ->with('equipment')
            ->with('reason_name')
            ->with('solution_name')
            ->with('consequence_name')
            ->with('recommendation_name')
            ->with('consequence_name')
        ->get();

        $totalMcscrCount = Mcscr::where('equipment_id',$id)->count();
        $totalTaskCount = TaskMcscr::where('equipment_id',$id)->count();
        $totalFuelCount = Fuel::where('equipment_id',$id)->sum('value');

        //1 hodometro
        //2 horimetro

        $totalHourCount1 = HoursDistanceEquipment::where('equipment_id',$id)->where('distance_control_id',2)->orderBy('date','asc')->first();
        $totalHourCount2 = HoursDistanceEquipment::where('equipment_id',$id)->where('distance_control_id',2)->orderBy('date','desc')->first();

        $totalDistanceCount1 = HoursDistanceEquipment::where('equipment_id',$id)->where('distance_control_id',1)->orderBy('date','asc')->first();
        $totalDistanceCount2 = HoursDistanceEquipment::where('equipment_id',$id)->where('distance_control_id',1)->orderBy('date','desc')->first();


        

        if($totalHourCount1 == null || $totalHourCount2 == null){
            $totalHourCount = 0;
        }else{
            $totalHourCount = $totalHourCount2->value -  $totalHourCount1->value;
        }

        

        if($totalDistanceCount1 == null || $totalDistanceCount2 == null){
            $totalDistanceCount = 0;
        }else{
            $totalDistanceCount = $totalDistanceCount2->value -  $totalDistanceCount1->value;
        }

        $dataChartAvailable = [];
        $dataChartUnAvailable = [];

        $dataChartMCSCR = [];
        $dataChartTaskMcscr = [];

        $dataChartMaterialMCSCR = [];
        $dataChartMaterialTaskMcscr = [];

        $dataChartLaborMCSCR = [];
        $dataChartLaborTaskMcscr = [];


        for($i=1; $i<=12; $i++){
            
            $mcscrThisYear = Mcscr::where('mcscr_status_id',1)->whereMonth('opened_at',$i)->where('equipment_id',$id)->whereYear('opened_at',date('Y'))->sum('total_hours');
            $mcscrLastYear = Mcscr::where('mcscr_status_id',1)->whereMonth('opened_at',$i)->where('equipment_id',$id)->whereYear('opened_at',date('Y',strtotime('-1 year')))->sum('total_hours');
            if($mcscrThisYear == 0){
                
                $dataChartAvailable[] = 100;

            }else{

                if(100 - round($mcscrThisYear*100/730,2) > 100 || 100 - round($mcscrThisYear*100/730,2) < 0){
                    $dataChartAvailable[] = 0;
                }else{
                    $dataChartAvailable[] = 100 - round($mcscrThisYear*100/730,2);
                } 
            }

            if($mcscrLastYear == 0){
                
                $dataChartUnAvailable[] = 100;

            }else{

                if(100 - round($mcscrLastYear*100/730,2) > 100 || 100 - round($mcscrLastYear*100/730,2) < 0){
                    $dataChartUnAvailable[] = 0;
                }else{
                    $dataChartUnAvailable[] = 100 - round($mcscrLastYear*100/730,2);
                } 
            }
        }

        for($i=1; $i<=12; $i++){
            $mcscr = Mcscr::whereMonth('opened_at',$i)->where('equipment_id',$id)->whereYear('opened_at',date('Y'))->get();
            $taskmcscr = TaskMcscr::whereMonth('opened_at',$i)->where('equipment_id',$id)->whereYear('opened_at',date('Y'))->get();

            $dataChartMCSCR[]=$mcscr->count() ;
            $dataChartTaskMcscr[]=$taskmcscr->count() ;

            $dataChartMaterialMCSCR[]=$mcscr->sum('material_cost');
            $dataChartMaterialTaskMcscr[]=$taskmcscr->sum('material_cost');

            $dataChartLaborMCSCR[]=$mcscr->sum('material_labor');
            $dataChartLaborTaskMcscr[]=$taskmcscr->sum('material_labor');
        }
        

        $dataChartFuelMonth = [];
        $dataChartFuelDay = [];

        for ($x = 1; $x <= 31; $x++) {
            $fuelChartDay = Fuel::whereDay('date',$x)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id',$id)->first();

            if($fuelChartDay==null){
                $dataChartFuelDay[]=0;
            }else{
                $dataChartFuelDay[]=$fuelChartDay->value;
            }
 
        }

        for($i=1; $i<=12; $i++){
            $fuelChartMonth = Fuel::whereMonth('date',$i)->whereYear('date',date('Y'))->where('equipment_id',$id)->sum('value');

            $dataChartFuelMonth[]=$fuelChartMonth;
 
        }

        // $dataChartHourMonth = [];
        $dataChartDistanceMonth = [];

        // $totalHourCount1 = HoursDistanceEquipment::where('type_equipment_id',$id)->where('distance_control_id',2)->orderBy('date','asc')->first();
        // $totalHourCount2 = HoursDistanceEquipment::where('type_equipment_id',$id)->where('distance_control_id',2)->orderBy('date','desc')->first();

        // $totalDistanceCount1 = HoursDistanceEquipment::where('type_equipment_id',$id)->where('distance_control_id',1)->orderBy('date','asc')->first();
        // $totalDistanceCount2 = HoursDistanceEquipment::where('type_equipment_id',$id)->where('distance_control_id',1)->orderBy('date','desc')->first();


        

        // if($totalHourCount1 == null || $totalHourCount2 == null){
        //     $totalHourCount = 0;
        // }else{
        //     $totalHourCount = $totalHourCount2->value -  $totalHourCount1->value;
        // }

        

        // if($totalDistanceCount1 == null || $totalDistanceCount2 == null){
        //     $totalDistanceCount = 0;
        // }else{
        //     $totalDistanceCount = $totalDistanceCount2->value -  $totalDistanceCount1->value;
        // }

        for($i=1; $i<=12; $i++){
            // $hourChartMonth1 = HoursDistanceEquipment::whereMonth('date',$i)->whereYear('date',date('Y'))->where('equipment_id',$id)->where('distance_control_id',2)->max('value');
            // $hourChartMonth2 = HoursDistanceEquipment::whereMonth('date',$i)->whereYear('date',date('Y'))->where('equipment_id',$id)->where('distance_control_id',2)->min('value');

            $distanceChartMonth1 = HoursDistanceEquipment::whereMonth('date',$i)->whereYear('date',date('Y'))->where('equipment_id',$id)->where('distance_control_id',1)->max('value');
            $distanceChartMonth2 = HoursDistanceEquipment::whereMonth('date',$i)->whereYear('date',date('Y'))->where('equipment_id',$id)->where('distance_control_id',1)->min('value');

            // $dataChartHourMonth[]=$hourChartMonth1-$hourChartMonth2;
            $dataChartDistanceMonth[]=$distanceChartMonth1-$distanceChartMonth2;
 
        }


        $dataChartDistanceDay = [];

        $monthnumber = date('m');

        

       

        for ($x = 1; $x <= 31; $x++) {
            
            // if($x == 1){

            //     if($monthnumber == 1){
            //         //mothnumber 12
                   
            //         $distanceChartDay1 = HoursDistanceEquipment::whereDay('date',$x)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id',$id)->sum('value');
            //         $distanceChartDay2 = HoursDistanceEquipment::whereDay('date',30)->whereMonth('date',12)->whereYear('date',date('Y'))->where('equipment_id',$id)->sum('value');
            //     }else{
            //         $newMothNumber = $monthnumber - 1;

            //         $distanceChartDay1 = HoursDistanceEquipment::whereDay('date',$x)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id',$id)->sum('value');
            //         $distanceChartDay2 = HoursDistanceEquipment::whereDay('date',30)->whereMonth('date',$newMothNumber)->whereYear('date',date('Y'))->where('equipment_id',$id)->sum('value');
            //     }
            // }else{
            //     $newX = $x-1;

            //     $distanceChartDay1 = HoursDistanceEquipment::whereDay('date',$x)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id',$id)->sum('value');
            //     $distanceChartDay2 = HoursDistanceEquipment::whereDay('date',$newX )->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id',$id)->sum('value');
            // }

            // $value = $distanceChartDay1-$distanceChartDay2;

            // if($value < 0){
            //     $dataChartDistanceDay[]=0;
            // }else{
            //     $dataChartDistanceDay[]=$distanceChartDay1-$distanceChartDay2;
            // }
            $hoursDistanceValue = 0;
            $hoursDistanceValueNext = 0;
            $hourdistanceChart = HoursDistanceEquipment::whereDay('date',$x)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id', $id)->first();

            if($x+1 <= 31){
                $hourdistanceChartNext = HoursDistanceEquipment::whereDay('date',$x+1)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id', $id)->first();
            }

            $hoursDistanceValue = $hourdistanceChart==null ? 0 : $hourdistanceChart->value;
            $hoursDistanceValueNext = $hourdistanceChartNext==null ? 0 : $hourdistanceChartNext->value;

            // $dataChartHourDistance[]=$hoursDistanceValueNext == 0 ? ($hoursDistanceValue - $hoursDistanceValueNext) : ($hoursDistanceValueNext - $hoursDistanceValue);
            $dataChartDistanceDay[]=$hoursDistanceValueNext == 0 ? 0 : ($hoursDistanceValueNext - $hoursDistanceValue);

            
 
        }


        $dataChartTaskMcscrDone = [];
        $dataChartTaskMcscrScheduled = [];

        for($i=1; $i<=12; $i++){
            
            $taskmcscrscheduled = TaskMcscr::whereMonth('schedule_for',$i)->where('equipment_id',$id)->whereYear('schedule_for',date('Y'))->where('task_mcscr_status_id',4)->get();
            $taskmcscrdone = TaskMcscr::whereMonth('opened_at',$i)->where('equipment_id',$id)->whereYear('opened_at',date('Y'))->where('task_mcscr_status_id',2)->get();

            $dataChartTaskMcscrDone[]=$taskmcscrdone->count() ;
            $dataChartTaskMcscrScheduled[]=$taskmcscrscheduled->count() ;

            // $dataChartMaterialMCSCR[]=$mcscr->sum('material_cost');
            // $dataChartMaterialTaskMcscr[]=$taskmcscr->sum('material_cost');

            // $dataChartLaborMCSCR[]=$mcscr->sum('material_labor');
            // $dataChartLaborTaskMcscr[]=$taskmcscr->sum('material_labor');
        }

        $pieChartDuration = [];

        $count_temp = Mcscr::where('mcscr_status_id',1)->where('equipment_id',$id)->count();
        $count=1;
        if($count_temp == 0){
            $count=1;
        }else{
            $count=$count_temp;
        }

        $pie5 = Mcscr::where('mcscr_status_id',1)->where('equipment_id',$id)->sum(DB::raw('TIMESTAMPDIFF(MINUTE, opened_at, diagnosis_start_at)'));
        $pieChartDuration[]=round($pie5/$count,2);

        $pie1 = Mcscr::where('mcscr_status_id',1)->where('equipment_id',$id)->sum(DB::raw('TIMESTAMPDIFF(MINUTE, diagnosis_start_at, diagnosis_end_at)'));
        $pieChartDuration[]=round($pie1/$count,2);
        $pie2 = Mcscr::where('mcscr_status_id',1)->where('equipment_id',$id)->sum(DB::raw('TIMESTAMPDIFF(MINUTE, execution_start_at, execution_end_at)'));
        $pieChartDuration[]=round($pie2/$count,2);
        $pie3 = Mcscr::where('mcscr_status_id',1)->where('equipment_id',$id)->sum(DB::raw('TIMESTAMPDIFF(MINUTE, awaiting_approval_start_at, awaiting_approval_end_at)'));
        $pieChartDuration[]=round($pie3/$count,2);
        $pie4 = Mcscr::where('mcscr_status_id',1)->where('equipment_id',$id)->sum(DB::raw('TIMESTAMPDIFF(MINUTE, opened_at, closed_at)'));
        
        $opened_at_to_closed_at=round($pie4/$count,2);

       



        


        return [
            'equipment'=>$equipment,
            'components'=>$components,
            'criticals'=>$criticals,
            'equipmentstatuses'=>$equipmentstatuses,
            'distance_control'=>$distance_control,
            'totalMcscrCount'=>$totalMcscrCount,
            'totalTaskCount'=>$totalTaskCount,
            'totalFuelCount'=>$totalFuelCount,
            'totalHourCount'=>$totalHourCount,
            'totalDistanceCount'=>$totalDistanceCount,
            'dataChartUnAvailable'=>$dataChartUnAvailable,
            'dataChartAvailable'=>$dataChartAvailable,
            'dataChartMCSCR'=>$dataChartMCSCR,
            'dataChartTaskMcscr'=>$dataChartTaskMcscr,
            'dataChartMaterialMCSCR'=>$dataChartMaterialMCSCR ,
            'dataChartMaterialTaskMcscr'=>$dataChartMaterialTaskMcscr,
            'dataChartLaborMCSCR'=>$dataChartLaborMCSCR,
            'dataChartLaborTaskMcscr'=>$dataChartLaborTaskMcscr,
            'dataChartFuelDay'=>$dataChartFuelDay,
            'dataChartFuelMonth'=>$dataChartFuelMonth,
            // 'dataChartHourMonth'=>$dataChartHourMonth,
            'dataChartDistanceMonth'=>$dataChartDistanceMonth,
            'dataChartDistanceDay'=>$dataChartDistanceDay,
            'dataChartTaskMcscrDone'=>$dataChartTaskMcscrDone,
            'dataChartTaskMcscrScheduled'=>$dataChartTaskMcscrScheduled,
            'mcscrData'=>$mcscrData,
            'pieChartDuration'=>$pieChartDuration,
            'opened_at_to_closed_at'=>$opened_at_to_closed_at


        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $equipment = Equipment::find($id);
        $criticals = Criticaly::orderBy('name','asc')->get();
        $type_equipments = TypeEquipment::orderBy('name','asc')->get();
        $equipmentstatuses = EquipmentStatus::orderBy('name','asc')->get();
        $destinations = Destination::orderBy('name','asc')->get();
        $areas = Area::orderBy('name','asc')->get();
        $suppliers = Supplier::orderBy('name','asc')->get();
        $center_costs = CenterCost::orderBy('name','asc')->get();
        $center_cost_accounts = CenterCostAccount::where('center_cost_id',$equipment->center_cost_id)->orderBy('name','asc')->get();
        $acquisitions = Acquisition::orderBy('name','asc')->get();
        $distance_controls = DistanceControl::get();
        $coin = Coin::orderBy('id','asc')->get();
        $load_unity = LoadUnity::orderBy('id','asc')->get();
       

        return [
            'equipment'=>$equipment,
            'distance_controls'=>$distance_controls,
            'criticalies'=>$criticals,
            'type_equipments'=>$type_equipments,
            'equipment_statuses'=>$equipmentstatuses,
            'destinations'=>$destinations, 
            'suppliers'=>$suppliers,
            'areas' =>$areas,
            'center_costs' =>$center_costs,
            'center_cost_accounts' =>$center_cost_accounts,
            'acquisitions' =>$acquisitions,
            'coins'=>$coin,
            'load_unities'=>$load_unity
            ];
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();
        
        $equipment = Equipment::find($id);

        $equipment->update($data);

        if($data['equipment_status_id']==1){
            $components = EquipmentComponent::where('equipment_id',$id)->get();
            $sub_components = EquipmentSubComponent::where('equipment_id',$id)->get();

            foreach($components as $item){
                $item->update([
                    'equipment_status_id'=>1
                ]);
            }

            foreach($sub_components as $item){
                $item->update([
                    'equipment_status_id'=>1
                ]);
            }
        }
        return $equipment;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $equipment = Equipment::find($id);
        $components = EquipmentComponent::where('equipment_id',$id)->get();

        foreach($components as $item){
            $item->delete();
        }

        $equipment->delete();
        return 'success';

    }

    public function reconciliation($id){

        $equipment = Equipment::find($id);

        $typeequipment = TypeEquipment::find($equipment->type_equipment_id);

        $typeequipmentcomponent = TypeEquipmentComponent::where('type_equipment_id',$equipment->type_equipment_id)->get();

        

        foreach($typeequipmentcomponent as $item){
            $equipmentcomponent = EquipmentComponent::where('equipment_id',$id)->where('type_equipment_component_id',$item->id)->first();

            if($equipmentcomponent == null){
                $created_equipment_component = EquipmentComponent::create([
                    'name'=>$item->name,
                    'criticaly_id'=>$item->criticaly_id,
                    'equipment_id'=>$id,
                    'equipment_status_id'=>1,
                    'percentage_weigth'=>$item->percentage_weigth,
                    'type_equipment_component_id'=>$item->id,
                    'model'=>$item->model,
                    'make'=>$item->make,
                ]);
                $subcomponents = TypeEquipmentSubComponent::where('type_equipment_component_id',$item->id)->get();

                foreach($subcomponents as $item2){
                    EquipmentSubComponent::create([
                        'name'=>$item2->name,
                        'criticaly_id'=>$item2->criticaly_id,
                        'equipment_component_id'=>$created_equipment_component->id,
                        'equipment_id'=>$id,
                        'equipment_status_id'=>1,
                        'percentage_weigth'=>$item2->percentage_weigth,
                        'type_equipment_sub_component_id'=>$item2->id,
                        'model'=>$item2->model,
                        'make'=>$item2->make,
                    ]);
                }
            }else{

                $equipmentcomponent->update([
                    'name'=>$item->name,
                    'criticaly_id'=>$item->criticaly_id,
                    'equipment_id'=>$id,
                    'percentage_weigth'=>$item->percentage_weigth,
                    'type_equipment_component_id'=>$item->id,
                    'model'=>$item->model,
                    'make'=>$item->make,
                ]);

                $existent_subcomponents = TypeEquipmentSubComponent::where('type_equipment_component_id',$equipmentcomponent->type_equipment_component_id)->get();

                foreach($existent_subcomponents as $item3){
                    $equipment_sub_component_test = EquipmentSubComponent::where('equipment_id',$id)->where('equipment_component_id',$equipmentcomponent->id)->where('type_equipment_sub_component_id',$item3->id)->first();
                    
                    if($equipment_sub_component_test == null){
                        EquipmentSubComponent::create([
                            'name'=>$item3->name,
                            'criticaly_id'=>$item3->criticaly_id,
                            'equipment_component_id'=>$equipmentcomponent->id,
                            'equipment_id'=>$id,
                            'equipment_status_id'=>1,
                            'percentage_weigth'=>$item3->percentage_weigth,
                            'type_equipment_sub_component_id'=>$item3->id,
                            'model'=>$item3->model,
                            'make'=>$item3->make,
                        ]);
                    }else{
                        $equipment_sub_component_test->update([
                            'name'=>$item3->name,
                            'criticaly_id'=>$item3->criticaly_id,
                            'percentage_weigth'=>$item3->percentage_weigth,
                            'type_equipment_sub_component_id'=>$item3->id,
                            'model'=>$item3->model,
                            'make'=>$item3->make,
                        ]);
                    }
                }

            }
        }


        $equipment_current = Equipment::with('destination')
        ->with('area')
        ->with('supplier')
        ->with('type_equipment')
        ->with('equipment_status')
        ->with('criticaly')
        ->with('acquisition')
        ->with('center_cost')
        ->with('distance_control')
        ->with('center_cost_account')
        ->with('coin')
        ->with('load_unity')
        ->find($id);
        
        $criticals_current = Criticaly::orderBy('name','asc')->get();
        $equipmentstatuses_current = EquipmentStatus::orderBy('name','asc')->get();

        $distance_control_current = DistanceControl::get();

        

        $searchQuery = request('query');

        $components_current = EquipmentComponent::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%");
        })
        ->with('equipmentstatus')
            ->with('subcomponents')
        ->with('criticality')
        ->where('equipment_id',$id)
        ->orderBy('name','asc')
        ->paginate();
        

        return [
            'equipment'=>$equipment_current,
            'components'=>$components_current,
            'criticals'=>$criticals_current,
            'equipmentstatuses'=>$equipmentstatuses_current,
            'distance_control'=>$distance_control_current
        ];
    }


    public function mcscrcount($id){

        $totalMcscrCount = Mcscr::query()
        ->when(request('date_range') === 'total',function($query){
            
        })
        ->when(request('date_range') === 'today',function($query){
            $query->whereBetween('created_at',[now()->today(),now()]);
        })
        ->when(request('date_range') === '30',function($query){
            $query->whereBetween('created_at',[now()->subDays(30),now()]);
        })
        ->when(request('date_range') === '60',function($query){
            $query->whereBetween('created_at',[now()->subDays(60),now()]);
        })
        ->when(request('date_range') === '360',function($query){
            $query->whereBetween('created_at',[now()->subDays(360),now()]);
        })
        ->when(request('date_range') === 'monthtodate',function($query){
            $query->whereBetween('created_at',[now()->firstOfMonth(),now()]);
        })
        ->when(request('date_range') === 'yeartodate',function($query){
            $query->whereBetween('created_at',[now()->firstOfYear(),now()]);
        })
        ->where('equipment_id',$id)
        ->count();


        return response()->json([
            'totalMcscrCount'=>$totalMcscrCount
        ]);
    }

    public function taskcount($id){

        $totalTaskCount = TaskMcscr::query()
        ->when(request('date_range') === 'total',function($query){
        })
        ->when(request('date_range') === 'today',function($query){
            $query->whereBetween('created_at',[now()->today(),now()]);
        })
        ->when(request('date_range') === '30',function($query){
            $query->whereBetween('created_at',[now()->subDays(30),now()]);
        })
        ->when(request('date_range') === '60',function($query){
            $query->whereBetween('created_at',[now()->subDays(60),now()]);
        })
        ->when(request('date_range') === '360',function($query){
            $query->whereBetween('created_at',[now()->subDays(360),now()]);
        })
        ->when(request('date_range') === 'monthtodate',function($query){
            $query->whereBetween('created_at',[now()->firstOfMonth(),now()]);
        })
        ->when(request('date_range') === 'yeartodate',function($query){
            $query->whereBetween('created_at',[now()->firstOfYear(),now()]);
        })
        ->where('equipment_id',$id)
        ->count();


        return response()->json([
            'totalTaskCount'=>$totalTaskCount
        ]);
    }


    public function fuelcount($id){

        $totalFuelCount = Fuel::query()
        ->when(request('date_range') === 'total',function($query){
            
        })
        ->when(request('date_range') === 'today',function($query){
            $query->whereBetween('date',[now()->today(),now()]);
        })
        ->when(request('date_range') === '30',function($query){
            $query->whereBetween('date',[now()->subDays(30),now()]);
        })
        ->when(request('date_range') === '60',function($query){
            $query->whereBetween('date',[now()->subDays(60),now()]);
        })
        ->when(request('date_range') === '360',function($query){
            $query->whereBetween('date',[now()->subDays(360),now()]);
        })
        ->when(request('date_range') === 'monthtodate',function($query){
            $query->whereBetween('date',[now()->firstOfMonth(),now()]);
        })
        ->when(request('date_range') === 'yeartodate',function($query){
            $query->whereBetween('date',[now()->firstOfYear(),now()]);
        })
        ->where('equipment_id',$id)
        ->sum('value');


        return response()->json([
            'totalFuelCount'=>$totalFuelCount
        ]);
    }

    public function hourdistancecount($id){

        $totalDistanceCount1 = HoursDistanceEquipment::query()
        ->when(request('date_range') === 'total',function($query){
            
        })
        ->when(request('date_range') === 'today',function($query){
            $query->whereBetween('date',[now()->today(),now()]);
        })
        ->when(request('date_range') === '30',function($query){
            $query->whereBetween('date',[now()->subDays(30),now()]);
        })
        ->when(request('date_range') === '60',function($query){
            $query->whereBetween('date',[now()->subDays(60),now()]);
        })
        ->when(request('date_range') === '360',function($query){
            $query->whereBetween('date',[now()->subDays(360),now()]);
        })
        ->when(request('date_range') === 'monthtodate',function($query){
            $query->whereBetween('date',[now()->firstOfMonth(),now()]);
        })
        ->when(request('date_range') === 'yeartodate',function($query){
            $query->whereBetween('date',[now()->firstOfYear(),now()]);
        })
        ->where('equipment_id',$id)
        ->where('distance_control_id',1)
        ->orderBy('date','asc')->first();

        $totalDistanceCount2 = HoursDistanceEquipment::query()
        ->when(request('date_range') === 'total',function($query){
            
        })
        ->when(request('date_range') === 'today',function($query){
            $query->whereBetween('date',[now()->today(),now()]);
        })
        ->when(request('date_range') === '30',function($query){
            $query->whereBetween('date',[now()->subDays(30),now()]);
        })
        ->when(request('date_range') === '60',function($query){
            $query->whereBetween('date',[now()->subDays(60),now()]);
        })
        ->when(request('date_range') === '360',function($query){
            $query->whereBetween('date',[now()->subDays(360),now()]);
        })
        ->when(request('date_range') === 'monthtodate',function($query){
            $query->whereBetween('date',[now()->firstOfMonth(),now()]);
        })
        ->when(request('date_range') === 'yeartodate',function($query){
            $query->whereBetween('date',[now()->firstOfYear(),now()]);
        })
        ->where('equipment_id',$id)
        ->where('distance_control_id',1)
        ->orderBy('date','desc')
        ->first();


        if($totalDistanceCount1 == null || $totalDistanceCount2 == null){
            $totalDistanceCount = 0;
        }else{
            $totalDistanceCount = $totalDistanceCount2->value -  $totalDistanceCount1->value;
        }

        $totalHourCount1 = HoursDistanceEquipment::query()
        ->when(request('date_range') === 'total',function($query){
            
        })
        ->when(request('date_range') === 'today',function($query){
            $query->whereBetween('date',[now()->today(),now()]);
        })
        ->when(request('date_range') === '30',function($query){
            $query->whereBetween('date',[now()->subDays(30),now()]);
        })
        ->when(request('date_range') === '60',function($query){
            $query->whereBetween('date',[now()->subDays(60),now()]);
        })
        ->when(request('date_range') === '360',function($query){
            $query->whereBetween('date',[now()->subDays(360),now()]);
        })
        ->when(request('date_range') === 'monthtodate',function($query){
            $query->whereBetween('date',[now()->firstOfMonth(),now()]);
        })
        ->when(request('date_range') === 'yeartodate',function($query){
            $query->whereBetween('date',[now()->firstOfYear(),now()]);
        })
        ->where('equipment_id',$id)
        ->where('distance_control_id',2)
        ->orderBy('date','asc')->first();

        $totalHourCount2 = HoursDistanceEquipment::query()
        ->when(request('date_range') === 'total',function($query){
            
        })
        ->when(request('date_range') === 'today',function($query){
            $query->whereBetween('date',[now()->today(),now()]);
        })
        ->when(request('date_range') === '30',function($query){
            $query->whereBetween('date',[now()->subDays(30),now()]);
        })
        ->when(request('date_range') === '60',function($query){
            $query->whereBetween('date',[now()->subDays(60),now()]);
        })
        ->when(request('date_range') === '360',function($query){
            $query->whereBetween('date',[now()->subDays(360),now()]);
        })
        ->when(request('date_range') === 'monthtodate',function($query){
            $query->whereBetween('date',[now()->firstOfMonth(),now()]);
        })
        ->when(request('date_range') === 'yeartodate',function($query){
            $query->whereBetween('date',[now()->firstOfYear(),now()]);
        })
        ->where('equipment_id',$id)
        ->where('distance_control_id',2)
        ->orderBy('date','desc')
        ->first();




        if($totalHourCount1 == null || $totalHourCount2 == null){
            $totalHourCount = 0;
        }else{
            $totalHourCount = $totalHourCount2->value -  $totalHourCount1->value;
        }

        



        return response()->json([
            'totalDistanceCount'=>$totalDistanceCount,
            'totalHourCount'=>$totalHourCount,

        ]);
    }
}
