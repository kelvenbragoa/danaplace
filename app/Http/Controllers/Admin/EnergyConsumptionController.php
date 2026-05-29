<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnergyConsumption;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EnergyConsumptionController extends Controller
{
    public function index()
    {
        //
        $searchQuery = request('query');

            $energyConsumption = EnergyConsumption::query()
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

            return $energyConsumption;
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

        $test = EnergyConsumption::where('equipment_id',$equipment->id)->where('date',$data['date'])->first();

        if($test != null){
            return response()->json([
                'message' => 'Não foi possível a leitura para esta data. Já existe um registo.',
            ], 404);
        }

        $energyConsumption = EnergyConsumption::create([
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
        $energyConsumption = EnergyConsumption::
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
            $energyConsumptionChart = EnergyConsumption::whereDay('date',$x)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id', $energyConsumption->equipment_id)->first();

            if($x+1 <= 31){
                $energyConsumptionChartNext = EnergyConsumption::whereDay('date',$x+1)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id', $energyConsumption->equipment_id)->first();
            }

            $hoursDistanceValue = $energyConsumptionChart==null ? 0 : $energyConsumptionChart->value;
            $hoursDistanceValueNext = $energyConsumptionChartNext==null ? 0 : $energyConsumptionChartNext->value;

            // $dataChartHourDistance[]=$hoursDistanceValueNext == 0 ? ($hoursDistanceValue - $hoursDistanceValueNext) : ($hoursDistanceValueNext - $hoursDistanceValue);
            $dataChartHourDistance[]=$hoursDistanceValueNext == 0 ? 0 : ($hoursDistanceValueNext - $hoursDistanceValue);


            // if($energyConsumptionChart==null){
            //     $dataChartHourDistance[]=0;
            // }else{
            //     $dataChartHourDistance[]=$energyConsumptionChart->value;
            // }
 
        }

        return [
            'hourdistance'=>$energyConsumption,
            'dataChartHourDistance'=>$dataChartHourDistance
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $energyConsumption = EnergyConsumption::
        with('distance_control')
        ->with('equipment')
        ->with('type_equipment')
        ->with('area')
        ->with('destination')
        ->find($id);
        return $energyConsumption;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();
        $energyConsumption = EnergyConsumption::find($id);
        $energyConsumption->update($data);
        return $energyConsumption;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $energyConsumption = EnergyConsumption::find($id);
        $energyConsumption->delete();
        return true;
    }
}
