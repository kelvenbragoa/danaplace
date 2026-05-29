<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\HoursDistanceEquipment;
use Illuminate\Http\Request;

class HoursDistanceEquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $hourdistance = HoursDistanceEquipment::query()
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

            return $hourdistance;
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

        $test = HoursDistanceEquipment::where('equipment_id',$equipment->id)->where('date',$data['date'])->first();

        if($test != null){
            return response()->json([
                'message' => 'Não foi possível a leitura para esta data. Já existe um registo.',
            ], 404);
        }

        $hourdistance = HoursDistanceEquipment::create([
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
        $hourdistance = HoursDistanceEquipment::
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
            $hourdistanceChart = HoursDistanceEquipment::whereDay('date',$x)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id', $hourdistance->equipment_id)->first();

            if($x+1 <= 31){
                $hourdistanceChartNext = HoursDistanceEquipment::whereDay('date',$x+1)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id', $hourdistance->equipment_id)->first();
            }

            $hoursDistanceValue = $hourdistanceChart==null ? 0 : $hourdistanceChart->value;
            $hoursDistanceValueNext = $hourdistanceChartNext==null ? 0 : $hourdistanceChartNext->value;

            // $dataChartHourDistance[]=$hoursDistanceValueNext == 0 ? ($hoursDistanceValue - $hoursDistanceValueNext) : ($hoursDistanceValueNext - $hoursDistanceValue);
            $dataChartHourDistance[]=$hoursDistanceValueNext == 0 ? 0 : ($hoursDistanceValueNext - $hoursDistanceValue);


            // if($hourdistanceChart==null){
            //     $dataChartHourDistance[]=0;
            // }else{
            //     $dataChartHourDistance[]=$hourdistanceChart->value;
            // }
 
        }

        return [
            'hourdistance'=>$hourdistance,
            'dataChartHourDistance'=>$dataChartHourDistance
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $hourdistance = HoursDistanceEquipment::
        with('distance_control')
        ->with('equipment')
        ->with('type_equipment')
        ->with('area')
        ->with('destination')
        ->find($id);
        return $hourdistance;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();
        $hourdistance = HoursDistanceEquipment::find($id);
        $hourdistance->update($data);
        return $hourdistance;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $hourdistance = HoursDistanceEquipment::find($id);
        $hourdistance->delete();
        return true;
    }
}
