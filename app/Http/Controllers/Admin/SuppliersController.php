<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SuppliersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $suppliers = Supplier::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('equipments')
            ->with('available_equipments')
            ->with('unavailable_equipments')
            ->with('imobilized_equipments')
            ->orderBy('name','asc')
            ->paginate();

            return $suppliers;
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
        $supplier = Supplier::create($data);
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
        $supplier = Supplier::
        with('equipments')
            ->with('available_equipments')
            ->with('unavailable_equipments')
            ->with('imobilized_equipments')
        ->find($id);

        $searchQuery = request('query');

        $equipments = Equipment::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%");
        })
        ->with('destination')
        ->with('area')
        ->with('supplier')
        ->with('type_equipment')
        ->with('equipment_status')
        ->with('criticaly')
        ->with('acquisition')
        ->with('center_cost')
        ->with('distance_control')
        ->with('center_cost_account')
        ->where('supplier_id',$id)
        ->orderBy('name','asc')
        ->paginate();


        return [
            'supplier'=>$supplier,
            'equipments'=>$equipments
        
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $supplier = Supplier::find($id);
        


        return $supplier;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $supplier = Supplier::find($id);

        $supplier->update($data);

        return $supplier;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $supplier = Supplier::find($id);

        $supplier->delete();

        return true;
    }
}
