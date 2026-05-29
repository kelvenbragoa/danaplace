<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\WaterConsumption;
use Illuminate\Http\Request;

class WaterConsumptionController extends Controller
{
    public function index()
    {
        //
        $searchQuery = request('query');

            $waterConsumption = WaterConsumption::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('id','like',"%{$searchQuery}%");
            })
            ->with('distance_control')
            ->with('equipment')
            ->with('type_equipment')
            ->with('area')
            ->with('destination')
            ->orderBy('date','desc')
            ->paginate();

            return $waterConsumption;
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

        $test = WaterConsumption::where('equipment_id',$equipment->id)->where('date',$data['date'])->first();

        if($test != null){
            return response()->json([
                'message' => 'Não foi possível a leitura para esta data. Já existe um registo.',
            ], 404);
        }

        $waterConsumption = WaterConsumption::create([
            'equipment_id'=>$data['equipment_id'],
            'type_equipment_id'=>$data['type_equipment_id'],
            'destination_id'=>$equipment->destination_id,
            'area_id'=>$equipment->area_id,
            'date'=>$data['date'],
            'distance_control_id'=>$equipment->distance_control_id,
            'value'=>$data['value'],
        ]);
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
        $waterConsumption = WaterConsumption::
        with('distance_control')
        ->with('equipment')
        ->with('type_equipment')
        ->with('area')
        ->with('destination')
        ->find($id);

        $dataChartHourDistance = [];
        

        for ($x = 1; $x <= 31; $x++) {
            $hoursDistanceValue = 0;
            $hoursDistanceValueNext = 0;
            $waterConsumptionChart = WaterConsumption::whereDay('date',$x)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id', $waterConsumption->equipment_id)->first();

            if($x+1 <= 31){
                $waterConsumptionChartNext = WaterConsumption::whereDay('date',$x+1)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id', $waterConsumption->equipment_id)->first();
            }

            $hoursDistanceValue = $waterConsumptionChart==null ? 0 : $waterConsumptionChart->value;
            $hoursDistanceValueNext = $waterConsumptionChartNext==null ? 0 : $waterConsumptionChartNext->value;

            // $dataChartHourDistance[]=$hoursDistanceValueNext == 0 ? ($hoursDistanceValue - $hoursDistanceValueNext) : ($hoursDistanceValueNext - $hoursDistanceValue);
            $dataChartHourDistance[]=$hoursDistanceValueNext == 0 ? 0 : ($hoursDistanceValueNext - $hoursDistanceValue);


            // if($waterConsumptionChart==null){
            //     $dataChartHourDistance[]=0;
            // }else{
            //     $dataChartHourDistance[]=$waterConsumptionChart->value;
            // }
 
        }

        return [
            'hourdistance'=>$waterConsumption,
            'dataChartHourDistance'=>$dataChartHourDistance
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $waterConsumption = WaterConsumption::
        with('distance_control')
        ->with('equipment')
        ->with('type_equipment')
        ->with('area')
        ->with('destination')
        ->find($id);
        return $waterConsumption;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();
        $waterConsumption = WaterConsumption::find($id);
        $waterConsumption->update($data);
        return $waterConsumption;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $waterConsumption = WaterConsumption::find($id);
        $waterConsumption->delete();
        return true;
    }
}
