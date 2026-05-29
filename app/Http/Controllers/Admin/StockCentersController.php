<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockCenter;
use Illuminate\Http\Request;

class StockCentersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $stockcenters = StockCenter::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('stockcenterproducts.stockproduct')
            ->orderBy('name','asc')
            ->paginate();

            return $stockcenters;
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
        $stockcenter = StockCenter::create($data);
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
        $stockcenter = StockCenter::with('stockcenterproducts.stockproduct.brand')->with('stockcenterproducts.stockproduct.category')->with('stockcenterproducts.stockproduct.iva')->find($id);

        return [
            'centerstock'=>$stockcenter,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $stockcenter = StockCenter::find($id);
        return $stockcenter;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();
        $stockcenter = StockCenter::find($id);
        $stockcenter->update($data);
        return $stockcenter;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $stockcenter = StockCenter::find($id);
        $stockcenter->delete();
        return true;
    }
}
