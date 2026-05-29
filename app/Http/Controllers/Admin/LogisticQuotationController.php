<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogisticQuotation;
use Illuminate\Http\Request;

class LogisticQuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

        $logisticquotation = LogisticQuotation::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('customer_name','like',"%{$searchQuery}%");
        })
        ->with('customer')
        ->with('tripdestination')
        ->with('typeload')
        ->with('status')
        ->orderBy('id','asc')
        ->paginate();

        return response()->json([
            'logisticquotation' => $logisticquotation
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
        $logisticquotation = LogisticQuotation::create($data);

        return response()->json([
            'logisticquotation' => $logisticquotation
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $logisticquotation = LogisticQuotation::with('customer')
        ->with('tripdestination')
        ->with('typeload')
        ->with('status')->findOrFail($id);

        return response()->json([
            'logisticquotation' => $logisticquotation
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $logisticquotation = LogisticQuotation::with('customer')
        ->with('tripdestination')
        ->with('typeload')
        ->with('status')->findOrFail($id);
        // $trip = Trip::all();

        return response()->json([
            'logisticquotation' => $logisticquotation,
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
        $logisticquotation = LogisticQuotation::findOrFail($id);

        $logisticquotation->update($data);

        return response()->json([
            'logisticquotation' => $logisticquotation
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $logisticquotation = LogisticQuotation::findOrFail($id);

        $logisticquotation->delete();

        return response()->noContent();
    }
}
