<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Acquisition;
use App\Models\Area;
use App\Models\CenterCost;
use App\Models\CenterCostAccount;
use App\Models\Coin;
use App\Models\Criticaly;
use App\Models\Destination;
use App\Models\DistanceControl;
use App\Models\EnergyConsumption;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentComponent;
use App\Models\EquipmentFee;
use App\Models\EquipmentStatus;
use App\Models\EquipmentSubComponent;
use App\Models\EquipmentUpload;
use App\Models\Fee;
use App\Models\Fuel;
use App\Models\HoursDistanceEquipment;
use App\Models\LoadUnity;
use App\Models\Mcscr;
use App\Models\Supplier;
use App\Models\TaskMcscr;
use App\Models\TypeEquipment;
use App\Models\TypeEquipmentComponent;
use App\Models\TypeEquipmentSubComponent;
use App\Models\WaterConsumption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class EquipmentsController extends Controller
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
        ->when(request('destination'),function($query,$destination){
            $query->where('destination_id',$destination);
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
        ->orderBy('id','asc')
        ->paginate();

        $destinations = Destination::get();

        
        return response()->json([
            'equipments'=>$equipments,
            'destinations'=>$destinations
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
        
        $equipment = Equipment::create($data);

        foreach($data['fee_ids'] as $fee_id){
            EquipmentFee::create([
                'equipment_id'=>$equipment->id,
                'fee_id'=>$fee_id
            ]);
            
        }


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
            ->orderBy('opened_at','desc')
        ->get();

        $totalMcscrCount = Mcscr::where('equipment_id',$id)->count();
        $totalTaskCount = TaskMcscr::where('equipment_id',$id)->count();
        $totalFuelCount = Fuel::where('equipment_id',$id)->sum('value');

        $totalEnergyCount = EnergyConsumption::where('equipment_id',$id)->sum('value');
        $totalWaterCount = WaterConsumption::where('equipment_id',$id)->sum('value');

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

        $month = date('m');


        for($i=1; $i<=12; $i++){
            
            $mcscrThisYear = Mcscr::where('mcscr_status_id',1)->whereMonth('opened_at',$i)->where('equipment_id',$id)->whereYear('opened_at',date('Y'))->sum('total_hours');
            $mcscrLastYear = Mcscr::where('mcscr_status_id',1)->whereMonth('opened_at',$i)->where('equipment_id',$id)->whereYear('opened_at',date('Y',strtotime('-1 year')))->sum('total_hours');
            if($mcscrThisYear == 0){
                
                $dataChartAvailable[] = 0;

            }else{

                if(100 - round($mcscrThisYear*100/730,2) > 100 || 100 - round($mcscrThisYear*100/730,2) < 0){
                    $dataChartAvailable[] = 0;
                }else{
                    $dataChartAvailable[] = 100 - round($mcscrThisYear*100/730,2);
                } 
            }

            if($mcscrLastYear == 0){
                
                $dataChartUnAvailable[] = 0;

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

        $dataChartWaterMonth = [];
        $dataChartWaterDay = [];

        $dataChartEnergyMonth = [];
        $dataChartEnergyDay = [];

        for ($x = 1; $x <= 31; $x++) {
            $fuelChartDay = Fuel::whereDay('date',$x)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id',$id)->first();

            if($fuelChartDay==null){
                $dataChartFuelDay[]=0;
            }else{
                $dataChartFuelDay[]=$fuelChartDay->value;
            }

            $waterChartDay = WaterConsumption::whereDay('date',$x)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id',$id)->first();

            if($waterChartDay==null){
                $dataChartWaterDay[]=0;
            }else{
                $dataChartWaterDay[]=$waterChartDay->value;
            }

            $energyChartDay = EnergyConsumption::whereDay('date',$x)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id',$id)->first();

            if($energyChartDay==null){
                $dataChartEnergyDay[]=0;
            }else{
                $dataChartEnergyDay[]=$energyChartDay->value;
            }
 
        }

        for($i=1; $i<=12; $i++){
            $fuelChartMonth = Fuel::whereMonth('date',$i)->whereYear('date',date('Y'))->where('equipment_id',$id)->sum('value');
            $dataChartFuelMonth[]=$fuelChartMonth;

            $waterChartMonth = WaterConsumption::whereMonth('date',$i)->whereYear('date',date('Y'))->where('equipment_id',$id)->sum('value');
            $dataChartWaterMonth[]=$waterChartMonth;

            $energyChartMonth = Fuel::whereMonth('date',$i)->whereYear('date',date('Y'))->where('equipment_id',$id)->sum('value');
            $dataChartEnergyMonth[]=$energyChartMonth;
 
        }

        $dataChartDistanceMonth = [];


        for($i=1; $i<=12; $i++){

            $distanceChartMonth1 = HoursDistanceEquipment::whereMonth('date',$i)->whereYear('date',date('Y'))->where('equipment_id',$id)->where('distance_control_id',1)->max('value');
            $distanceChartMonth2 = HoursDistanceEquipment::whereMonth('date',$i)->whereYear('date',date('Y'))->where('equipment_id',$id)->where('distance_control_id',1)->min('value');

            $dataChartDistanceMonth[]=$distanceChartMonth1-$distanceChartMonth2;
 
        }


        $dataChartDistanceDay = [];

        $monthnumber = date('m');
       

        for ($x = 1; $x <= 31; $x++) {
            $hoursDistanceValue = 0;
            $hoursDistanceValueNext = 0;
            $hourdistanceChart = HoursDistanceEquipment::whereDay('date',$x)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id', $id)->first();

            if($x+1 <= 31){
                $hourdistanceChartNext = HoursDistanceEquipment::whereDay('date',$x+1)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id', $id)->first();
            }
            $hoursDistanceValue = $hourdistanceChart==null ? 0 : $hourdistanceChart->value;
            $hoursDistanceValueNext = $hourdistanceChartNext==null ? 0 : $hourdistanceChartNext->value;

            $dataChartDistanceDay[]=$hoursDistanceValueNext == 0 ? 0 : ($hoursDistanceValueNext - $hoursDistanceValue);
        }


        $dataChartTaskMcscrDone = [];
        $dataChartTaskMcscrScheduled = [];

        for($i=1; $i<=12; $i++){
            $taskmcscrscheduled = TaskMcscr::whereMonth('schedule_for',$i)->where('equipment_id',$id)->whereYear('schedule_for',date('Y'))->where('task_mcscr_status_id',4)->get();
            $taskmcscrdone = TaskMcscr::whereMonth('opened_at',$i)->where('equipment_id',$id)->whereYear('opened_at',date('Y'))->where('task_mcscr_status_id',2)->get();

            $dataChartTaskMcscrDone[]=$taskmcscrdone->count() ;
            $dataChartTaskMcscrScheduled[]=$taskmcscrscheduled->count() ;
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

       

        $profile = EquipmentUpload::where('equipment_id',$id)->first();

        if($profile == null){
            $profile_picture = '/files/img/sys/companylogo.png';
        }else{

            $profile->file = Storage::disk('s3')->temporaryUrl(
                $profile->file,
                now()->addMinutes(10),
                ['ResponseContentDisposition' => 'attachment']
            );

            $profile_picture = $profile->file;
        }

        $uploads = EquipmentUpload::where('equipment_id',$id)->get();

        foreach ($uploads as $item){
            $item->file = Storage::disk('s3')->temporaryUrl(
                $item->file,
                now()->addMinutes(10),
                ['ResponseContentDisposition' => 'attachment']
            );
        } 


        return [
            'profile_picture'=>$profile_picture,
            'uploads'=>$uploads,
            'equipment'=>$equipment,
            'components'=>$components,
            'criticals'=>$criticals,
            'equipmentstatuses'=>$equipmentstatuses,
            'distance_control'=>$distance_control,
            'totalMcscrCount'=>$totalMcscrCount,
            'totalTaskCount'=>$totalTaskCount,
            'totalFuelCount'=>$totalFuelCount,
            'totalEnergyCount'=>$totalEnergyCount,
            'totalWaterCount'=>$totalWaterCount,
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

            'dataChartWaterDay'=>$dataChartWaterDay,
            'dataChartWaterMonth'=>$dataChartWaterMonth,

            'dataChartEnergyDay'=>$dataChartEnergyDay,
            'dataChartEnergyMonth'=>$dataChartEnergyMonth,

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
        $equipment = Equipment::with('fees')->find($id);
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
        $categories = EquipmentCategory::orderBy('id','asc')->get();
        $fees = Fee::get();


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
            'load_unities'=>$load_unity,
            "categories"=>$categories,
            'fees'=>$fees,
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

        
        EquipmentFee::where('equipment_id',$id)->delete();

        foreach($data['fee_ids'] as $fee_id){
            EquipmentFee::create([
                'equipment_id'=>$equipment->id,
                'fee_id'=>$fee_id
            ]);
        }
        

        

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

    public function energycount($id){

        $totalEnergyCount = Fuel::query()
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
            'totalEnergyCount'=>$totalEnergyCount
        ]);
    }

    public function watercount($id){

        $totalWaterCount = WaterConsumption::query()
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
            'totalWaterCount'=>$totalWaterCount
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


    public function viewupload($id){

        $equipment = Equipment::
        find($id);

        $upload = EquipmentUpload::where('equipment_id',$id)->get();

        foreach ($upload as $item){
            $item->file = Storage::disk('s3')->temporaryUrl(
                $item->file,
                now()->addMinutes(10),
                ['ResponseContentDisposition' => 'attachment']
            );
        }

        return response()->json([
            'equipment'=>$equipment,
            'uploads'=>$upload
        ],200);


    }

    public function upload(Request $request){


        $data = $request->all();
        $equipment = Equipment::
        find($data['equipment_id']);
        $allowedfileExtension=['pdf'];
        $files = $request->file('image');
        if($request->has('image')){
            // foreach($files as $file){
                $filename = $files->getClientOriginalName();
                $extension = $files->getClientOriginalExtension();
                $imagePath = $files->store('equipment-attachment','s3');
                 EquipmentUpload::create([
                        'file' => $imagePath,
                        'equipment_id' => $data['equipment_id'],
                        // 'type' => $data['type'],
                        'type' => 'profile',
                    ]);
            }
        // }

        $upload = EquipmentUpload::where('equipment_id',$data['equipment_id'])->get();

        foreach ($upload as $item){
            $item->file = Storage::disk('s3')->temporaryUrl(
                $item->file,
                now()->addMinutes(10),
                ['ResponseContentDisposition' => 'attachment']
            );
        }


        return response()->json([
            'equipment'=>$equipment,
            'uploads'=>$upload
        ],200);


    }

    public function deleteupload($id){
        $equipment = EquipmentUpload::find($id);

        $equipment->delete();

        return true;
    }

    public function copyequipment(string $id){
        $old_equipment = Equipment::find($id);

        $data = $old_equipment->toArray();
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        $data['name'] = 'COPY-' . $old_equipment->name;

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

        return response()->json([
            'equipment'=>$equipment
        ],200);


}
}