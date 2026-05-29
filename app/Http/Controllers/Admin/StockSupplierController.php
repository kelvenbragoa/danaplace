<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockSupplier;
use Illuminate\Http\Request;

class StockSupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $stocksuppliers = StockSupplier::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->orderBy('name','asc')
            ->paginate();

            return $stocksuppliers;
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
        $stocksupplier = StockSupplier::create($data);
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
        $stocksupplier = StockSupplier::find($id);

        $searchQuery = request('query');

       


        return [
            'stocksupplier'=>$stocksupplier,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $stocksupplier = StockSupplier::find($id);
        


        return $stocksupplier;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $stocksupplier = StockSupplier::find($id);

        $stocksupplier->update($data);

        return $stocksupplier;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $stocksupplier = StockSupplier::find($id);

        $stocksupplier->delete();

        return true;
    }
}
