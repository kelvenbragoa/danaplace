<?php

namespace App\Http\Controllers\Destination;

use App\Http\Controllers\Controller;
use App\Models\CompanyConfig;
use App\Models\Criticaly;
use App\Models\Destination;
use App\Models\Equipment;
use App\Models\Fuel;
use App\Models\HoursDistanceEquipment;
use App\Models\Mcscr;
use App\Models\TaskMcscr;
use App\Models\TypeEquipment;
use App\Models\TypeEquipmentComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TypeEquipmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            // $type_equipments = TypeEquipment::query()
            // ->when(request('query'),function($query,$searchQuery){
            //     $query->where('name','like',"%{$searchQuery}%");
            // })
            // ->with('equipments')
            // ->with('available_equipments')
            // ->with('unavailable_equipments')
            // ->with('imobilized_equipments')
            // ->with('components')
            // ->orderBy('name','asc')
            // ->paginate();

            $type_equipments = Equipment::where('destination_id',Auth::user()->destination_id)->get()->groupBy('type_equipment.name');


            return $type_equipments;
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
        $type_equipments = TypeEquipment::create($data);
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
        //
        $destination_id = Auth::user()->destination_id;
        $company = CompanyConfig::find(1);
        $destination = Destination::find($destination_id);
        $type_equipment = TypeEquipment::
        with('equipments')
            ->with('available_equipments')
            ->with('unavailable_equipments')
            ->with('imobilized_equipments')
            ->with('components')
        ->find($id);
        $criticals = Criticaly::get();

        $searchQuery = request('query');

        $equipments = Equipment::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%");
        })
        ->where('type_equipment_id',$id)
        ->where('destination_id',$destination_id)
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
        ->with('lastmcscr.mcscr_status')
        ->with('lastmcscr.reason_name')
        ->with('mcscrmonth')
        ->with('taskmcscrmonth')
        ->orderBy('name','asc')
        ->paginate();

        $totalMcscrCount = Mcscr::where('type_equipment_id',$id)->where('destination_id',$destination_id)->count();
        $totalTaskCount = TaskMcscr::where('type_equipment_id',$id)->where('destination_id',$destination_id)->count();
        $totalFuelCount = Fuel::where('type_equipment_id',$id)->where('destination_id',$destination_id)->sum('value');

       
        //1 hodometro
        //2 horimetro

        $totalHourCount1 = HoursDistanceEquipment::where('type_equipment_id',$id)->where('destination_id',$destination_id)->where('distance_control_id',2)->orderBy('date','asc')->first();
        $totalHourCount2 = HoursDistanceEquipment::where('type_equipment_id',$id)->where('destination_id',$destination_id)->where('distance_control_id',2)->orderBy('date','desc')->first();

        $totalDistanceCount1 = HoursDistanceEquipment::where('type_equipment_id',$id)->where('destination_id',$destination_id)->where('distance_control_id',1)->orderBy('date','asc')->first();
        $totalDistanceCount2 = HoursDistanceEquipment::where('type_equipment_id',$id)->where('destination_id',$destination_id)->where('distance_control_id',1)->orderBy('date','desc')->first();


        

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

        $dataChartFuelMonth = [];
        $dataChartFuelDay = [];

        $pieChartDuration = [];

        $count_temp = Mcscr::where('mcscr_status_id',1)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->count();
        $count=1;
        if($count_temp == 0){
            $count=1;
        }else{
            $count=$count_temp;
        }

        $pie5 = Mcscr::where('mcscr_status_id',1)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->sum(DB::raw('TIMESTAMPDIFF(MINUTE, opened_at, diagnosis_start_at)'));
        $pieChartDuration[]=round($pie5/$count,2);
        
        $pie1 = Mcscr::where('mcscr_status_id',1)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->sum(DB::raw('TIMESTAMPDIFF(MINUTE, diagnosis_start_at, diagnosis_end_at)'));
        $pieChartDuration[]=round($pie1/$count,2);
        $pie2 = Mcscr::where('mcscr_status_id',1)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->sum(DB::raw('TIMESTAMPDIFF(MINUTE, execution_start_at, execution_end_at)'));
        $pieChartDuration[]=round($pie2/$count,2);
        $pie3 = Mcscr::where('mcscr_status_id',1)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->sum(DB::raw('TIMESTAMPDIFF(MINUTE, awaiting_approval_start_at, awaiting_approval_end_at)'));
        $pieChartDuration[]=round($pie3/$count,2);
        $pie4 = Mcscr::where('mcscr_status_id',1)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->sum(DB::raw('TIMESTAMPDIFF(MINUTE, opened_at, closed_at)'));

        $opened_at_to_closed_at=round($pie4/$count,2);



        for ($x = 1; $x <= 31; $x++) {
            $fuelChartDay = Fuel::whereDay('date',$x)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('type_equipment_id',$id)->where('destination_id',$destination_id)->first();

            if($fuelChartDay==null){
                $dataChartFuelDay[]=0;
            }else{
                $dataChartFuelDay[]=$fuelChartDay->value;
            }
 
        }

        for($i=1; $i<=12; $i++){
            $fuelChartMonth = Fuel::whereMonth('date',$i)->whereYear('date',date('Y'))->where('type_equipment_id',$id)->where('destination_id',$destination_id)->sum('value');

            $dataChartFuelMonth[]=$fuelChartMonth;
 
        }

        for($i=1; $i<=12; $i++){
            $mcscrThisYear = Mcscr::where('mcscr_status_id',1)->whereMonth('opened_at',$i)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->whereYear('opened_at',date('Y'))->sum('total_hours');
            $mcscrLastYear = Mcscr::where('mcscr_status_id',1)->whereMonth('opened_at',$i)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->whereYear('opened_at',date('Y', strtotime('-1 year')))->sum('total_hours');

            if($mcscrThisYear == 0){
                if($equipments->count()==0){
                    $dataChartAvailable[] = 0;
                }else{
                    $dataChartAvailable[] = 100;
                }
            }else{

                if(100 - round($mcscrThisYear*100/730,2) > 100 || 100 - round($mcscrThisYear*100/730,2) < 0){
                    $dataChartAvailable[] = 0;
                }else{
                    $dataChartAvailable[] = 100 - round($mcscrThisYear*100/730,2);
                }
            }

            if($mcscrLastYear == 0){
                if($equipments->count()==0){
                    $dataChartUnAvailable[] = 0;
                }else{
                    $dataChartUnAvailable[] = 100;
                }
            }else{

                if(100 - round($mcscrLastYear*100/730,2) > 100 || 100 - round($mcscrLastYear*100/730,2) < 0){
                    $dataChartUnAvailable[] = 0;
                }else{
                    $dataChartUnAvailable[] = 100 - round($mcscrLastYear*100/730,2);
                }
            }
        }



        for($i=1; $i<=12; $i++){


            $mcscr = Mcscr::whereMonth('opened_at',$i)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->whereYear('opened_at',date('Y'))->get();
            $taskmcscr = TaskMcscr::whereMonth('opened_at',$i)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->whereYear('opened_at',date('Y'))->get();

            $dataChartMCSCR[]=$mcscr->count();
            $dataChartTaskMcscr[]=$taskmcscr->count();

            $dataChartMaterialMCSCR[]=$mcscr->sum('material_cost');
            $dataChartMaterialTaskMcscr[]=$taskmcscr->sum('material_cost');

            $dataChartLaborMCSCR[]=$mcscr->sum('material_labor');
            $dataChartLaborTaskMcscr[]=$taskmcscr->sum('material_labor');

        }


        $dataChartTaskMcscrDone = [];
        $dataChartTaskMcscrScheduled = [];

        for($i=1; $i<=12; $i++){
            
            $taskmcscrscheduled = TaskMcscr::whereMonth('schedule_for',$i)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->whereYear('schedule_for',date('Y'))->where('task_mcscr_status_id',4)->get();
            $taskmcscrdone = TaskMcscr::whereMonth('opened_at',$i)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->whereYear('opened_at',date('Y'))->where('task_mcscr_status_id',2)->get();

            $dataChartTaskMcscrDone[]=$taskmcscrdone->count();
            $dataChartTaskMcscrScheduled[]=$taskmcscrscheduled->count();


        }


        return[
            'destination'=>$destination,
            'type_equipment' =>$type_equipment,
            'criticals'=>$criticals,
            'equipments'=>$equipments,
            'totalMcscrCount'=>$totalMcscrCount,
            'totalTaskCount'=>$totalTaskCount,
            'totalFuelCount'=>$totalFuelCount,
            'totalHourCount'=>$totalHourCount,
            'totalDistanceCount'=>$totalDistanceCount,
            'dataChartUnAvailable'=>$dataChartUnAvailable,
            'dataChartAvailable'=>$dataChartAvailable,
            'dataChartMCSCR'=>$dataChartMCSCR,
            'dataChartTaskMcscr'=>$dataChartTaskMcscr,
            'dataChartFuelDay'=>$dataChartFuelDay,
            'dataChartFuelMonth'=>$dataChartFuelMonth,
            'dataChartMaterialMCSCR'=>$dataChartMaterialMCSCR ,
            'dataChartMaterialTaskMcscr'=>$dataChartMaterialTaskMcscr,
            'dataChartLaborMCSCR'=>$dataChartLaborMCSCR,
            'dataChartLaborTaskMcscr'=>$dataChartLaborTaskMcscr,
            'dataChartTaskMcscrDone'=>$dataChartTaskMcscrDone,
            'dataChartTaskMcscrScheduled'=>$dataChartTaskMcscrScheduled,
            'terminado' => Mcscr::where('mcscr_status_id',1)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->count(),
            'pendente' => Mcscr::where('mcscr_status_id',4)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->count(),
            'aprovacao' => Mcscr::where('mcscr_status_id',2)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->count(),
            'diagnostico' => Mcscr::where('mcscr_status_id',5)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->count(),
            'execucao' => Mcscr::where('mcscr_status_id',3)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->count(),
            'peca' => Mcscr::where('waiting_status_id',1)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->count(),
            'tecnico' => Mcscr::where('waiting_status_id',2)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->count(),
            'acidente' => Mcscr::where('waiting_status_id',3)->where('type_equipment_id',$id)->where('destination_id',$destination_id)->count(),
            'previsao'=>Mcscr::whereDate('output_forecast',today())->count(),
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

        $type_equipment = TypeEquipment::find($id);
        


        return $type_equipment;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $type_equipment = TypeEquipment::find($id);

        $type_equipment->update($data);

        return $type_equipment;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $type_equipment = TypeEquipment::find($id);

        $type_equipment->delete();

        return true;
    }

}
