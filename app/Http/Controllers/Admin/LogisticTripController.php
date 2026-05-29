<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Equipment;
use App\Models\LogisticCustomer;
use App\Models\LogisticDestinationExpense;
use App\Models\LogisticTrip;
use App\Models\LogisticTripDestination;
use App\Models\LogisticTripExpenses;
use App\Models\LogisticTripStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogisticTripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

        $trip = LogisticTrip::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%");
        })
        ->with('equipment')
        ->with('driver')
        ->with('destination')
        ->with('user')
        ->with('customer')
        ->with('tripstatus')
        ->orderBy('id','asc')
        ->paginate();

        return response()->json([
            'trip' => $trip
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $driver = Driver::all();
        $destination = LogisticTripDestination::orderBy('departure','asc')->get();
        $user = User::all();
        $tripstatus = LogisticTripStatus::all();
        $vehicle = Equipment::all();

        return response()->json([
            'driver'=>$driver,
            "destination"=>$destination,
            "user"=>$user,
            "tripstatus"=>$tripstatus,
            "vehicle"=>$vehicle,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->all();
        $destination = LogisticTripDestination::find($data["destination_id"]);
        $equipment = Equipment::find($data["equipment_id"]);
        $trip = LogisticTrip::create([
            "user_id"=>Auth::user()->id,
            "equipment_id"=>$data["equipment_id"],
            "type_equipment_id"=>$equipment->type_equipment_id,
            "area_id"=>$equipment->area_id,
            "driver_id"=>$data["driver_id"],
            "destination_id"=>$data["destination_id"],
            "trip_status_id"=>1,
            "customer_id"=>$data["customer_id"],
            'start_date'=>$data["start_date"],
            'end_date'=>$data["end_date"],
        ]);

        $expenses = LogisticDestinationExpense::where('destination_id',$data["destination_id"])->get();

        foreach($expenses as $item){
            LogisticTripExpenses::create([
                "trip_id"=>$trip->id,
                "expense_description"=>$item->expense_description,
                "expense_amount"=>$item->expense_amount,
                "destination_expense_id"=>$item->id,
            ]);
        }

        return response()->json([
            'trip' => $trip
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $trip = LogisticTrip::with('equipment.type_equipment')
        ->with('equipment.destination.province')
        ->with('driver')
        ->with('destination.loadstatus')
        ->with('user')
        ->with('customer')
        ->with('tripstatus')->findOrFail($id);
        $destination = LogisticTripDestination::where('id',$trip->destination_id)->with('loadstatus')->get();
        $expenses = LogisticTripExpenses::where("trip_id",$id)->orderBy('created_at','desc')->orderBy('id','desc')->get();

        return response()->json([
            'trip' => $trip,
            "destination" => $destination,
            // "dieselrequest" => $dieselrequest,
            "expense" => $expenses,
            'total_expense'=>$expenses->sum('expense_amount')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $trip = LogisticTrip::findOrFail($id);
        $driver = Driver::all();
        $destination = LogisticTripDestination::with('loadstatus')->get();
        $vehicle = Equipment::all();
        $user = User::all();
        $tripstatus = LogisticTripStatus::all();
        $customers = LogisticCustomer::orderBy('customer_name','asc')->get();


        return response()->json([
            
            'trip'=>$trip,
            'driver' => $driver,
            'destination' => $destination,
            'user' => $user,
            'tripstatuses' => $tripstatus,
            "vehicle"=>$vehicle,
            "customers"=>$customers
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();
        $trip = LogisticTrip::findOrFail($id);

        $trip->update($data);

        return response()->json([
            'trip' => $trip
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $trip = LogisticTrip::findOrFail($id);

        $trip->delete();

        return response()->noContent();
    }
}
