<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogisticCustomer;
use Illuminate\Http\Request;

class LogisticCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

        $logisticcustomer = LogisticCustomer::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('customer_name','like',"%{$searchQuery}%");
        })
        // ->with('trip')
        // ->with('loadstatus')
        ->orderBy('customer_name','asc')
        ->paginate();

        return response()->json([
            'logisticcustomer' => $logisticcustomer
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
        $logisticcustomer = LogisticCustomer::create($data);

        return response()->json([
            'logisticcustomer' => $logisticcustomer
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $logisticcustomer = LogisticCustomer::findOrFail($id);

        return response()->json([
            'logisticcustomer' => $logisticcustomer
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $logisticcustomer = LogisticCustomer::findOrFail($id);
        // $trip = Trip::all();

        return response()->json([
            'logisticcustomer' => $logisticcustomer,
            // 'trip'=>$trip
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();
        $logisticcustomer = LogisticCustomer::findOrFail($id);

        $logisticcustomer->update($data);

        return response()->json([
            'logisticcustomer' => $logisticcustomer
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $logisticcustomer = LogisticCustomer::findOrFail($id);

        $logisticcustomer->delete();

        return response()->noContent();
    }
}
