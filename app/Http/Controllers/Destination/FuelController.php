<?php

namespace App\Http\Controllers\Destination;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Fuel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class FuelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $fuel = Fuel::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('id','like',"%{$searchQuery}%");
            })
            ->with('equipment')
            ->with('type_equipment')
            ->with('area')
            ->with('destination')
            ->orderBy('date','desc')
            ->where('destination_id',Auth::user()->destination_id)
            ->paginate();

            return $fuel;
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

        // $test = Fuel::where('equipment_id',$equipment->id)->where('date',$data['date'])->first();

        // if($test != null){
        //     return response()->json([
        //         'message' => 'Não foi possível adicionar a leitura para esta data. Já existe um registo.',
        //     ], 404);
        // }

        $fuel = Fuel::create([
            'equipment_id'=>$data['equipment_id'],
            'type_equipment_id'=>$data['type_equipment_id'],
            'destination_id'=>$equipment->destination_id,
            'area_id'=>$equipment->area_id,
            'date'=>$data['date'],
            'distance_control_id'=>$equipment->distance_control_id,
            'value'=>$data['value'],
        ]);

        $equipment->update([
            'fuel'=>$equipment->fuel + $data['value']
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
        $fuel = Fuel::
        with('equipment')
        ->with('type_equipment')
        ->with('area')
        ->with('destination')
        ->find($id);

        $dataChartFuel = [];

        for ($x = 1; $x <= 31; $x++) {
            $fuelChart = Fuel::whereDay('date',$x)->whereMonth('date',date('m'))->whereYear('date',date('Y'))->where('equipment_id',$fuel->equipment_id)->first();

            if($fuelChart==null){
                $dataChartFuel[]=0;
            }else{
                $dataChartFuel[]=$fuelChart->value;
            }
 
        }

        return [
            'hourdistance'=>$fuel,
            'dataChartFuel'=>$dataChartFuel
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $fuel = Fuel::
        with('equipment')
        ->with('type_equipment')
        ->with('area')
        ->with('destination')
        ->find($id);
        return $fuel;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();
        $fuel = Fuel::find($id);
        $fuel->update($data);
        return $fuel;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $fuel = Fuel::find($id);
        $fuel->delete();
        return true;
    }
}
