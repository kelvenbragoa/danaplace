<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\LogisticDestinationExpense;
use App\Models\LogisticTripDestination;
use App\Models\LogisticTripLoadStatus;
use Illuminate\Http\Request;

class LogisticDestinationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

        $destination = LogisticTripDestination::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%");
        })
        ->with('loadstatus')
        ->with('coin')
        ->orderBy('departure','asc')
        ->paginate();

        return response()->json([
            'destination' => $destination
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $load = LogisticTripLoadStatus::get();

        return response()->json([
            'load' => $load,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->all();
        $destination = LogisticTripDestination::create($data);

        return response()->json([
            'destination' => $destination
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $destination = LogisticTripDestination::with('loadstatus')->with('coin')->findOrFail($id);
        $expenses = LogisticDestinationExpense::where('destination_id',$id)->get();

        return response()->json([
            'destination' => $destination,
            'expenses'=>$expenses,
            'total_expense'=>$expenses->sum('expense_amount')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $destination = LogisticTripDestination::findOrFail($id);
        $loadstatus = LogisticTripLoadStatus::all();
        $coins = Coin::orderBy('name','asc')->get();

        return response()->json([
            'destination' => $destination,
            'loadstatus'=>$loadstatus,
            'coins'=>$coins
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();
        $destination = LogisticTripDestination::findOrFail($id);

        $destination->update($data);

        return response()->json([
            'destination' => $destination
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $destination = LogisticTripDestination::findOrFail($id);

        $destination->delete();

        return response()->noContent();
    }
}
