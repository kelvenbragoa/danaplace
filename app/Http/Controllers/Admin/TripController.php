<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripExpense;
use Illuminate\Http\Request;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $trips = Trip::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('expenses')
            ->orderBy('id','desc')
            ->paginate();

            return $trips;
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

        $trips = Trip::create([
            'destination'=>$data['destination'],
            'departure_date'=>$data['departure_date'],
            'return_date'=>$data['return_date'],
            'name'=>$data['name'],
        ]);

        if($request->has('trip')){
            foreach($data['trip'] as $item){
                TripExpense::create([
                    'trip_id'=>$trips->id,
                    'description'=>$item['expense_description'],
                    'amount'=>$item['amount'],
                    'name'=>$item['expense_name'],
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
        $trips = Trip::find($id);
        $expenses = TripExpense::where('trip_id',$id)->get();
        return ['trips'=>$trips, 'expenses'=>$expenses];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $trips = Trip::find($id);
        $expenses = TripExpense::where('trip_id',$id)->get();
        return [
            'trips'=>$trips, 
            'expenses'=>$expenses
            ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $trips = Trip::find($id);

        $trips->update([
                'destination'=>$data['destination'],
                'departure_date'=>$data['departure_date'],
                'return_date'=>$data['return_date'],
                'name'=>$data['name'],
            ]);
        
        if($request->has('trip')){
            foreach($data['trip'] as $item){
                TripExpense::create([
                    'trip_id'=>$trips->id,
                    'description'=>$item['expense_description'],
                    'amount'=>$item['amount'],
                    'name'=>$item['expense_name'],
                ]);
            }
        }

        return $trips;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $trips = Trip::find($id);



        $trips->delete();

        return true;
    }
}
