<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Equipment;
use App\Models\Province;
use Illuminate\Http\Request;

class DestinationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

        $destinations = Destination::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%");
        })
        ->with('province')
        ->with('equipments')
        ->with('available_equipments')
        ->with('unavailable_equipments')
        ->with('imobilized_equipments')
        ->orderBy('name','asc')
        ->paginate();

        return $destinations;
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
        
        if(request('image')){
            $imagePath = request('image')->store('destination','public');
            // $image = Image::make(public_path("storage/{$imagePath}"))->fit(1000,1000);
            // $image->save();
            $imageArray = ['image'=> $imagePath ];
        }
        $destination = Destination::create(
            $data,
            $imageArray ?? []);
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
        $destination = Destination::
        with('province')
        ->with('equipments')
            ->with('available_equipments')
            ->with('unavailable_equipments')
            ->with('imobilized_equipments')
            ->with('task_mcscr')
            ->with('mcscr')
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
        ->where('destination_id',$id)
        ->orderBy('name','asc')
        ->paginate();

        $typeequipments = Equipment::where('destination_id',$id)->get()->groupBy('type_equipment.name');


        return [
            'destination'=>$destination,
            'equipments'=>$equipments,
            'typeequipments'=>$typeequipments
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $destination = Destination::find($id);
        

        $province = Province::orderBy('name','asc')->get();
        


        return [
            'destination'=>$destination,
            'provinces'=>$province
        ];

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //

        $destination = Destination::find($id);
        $data = $request->all();
        if(request('image')){
            $imagePath = request('image')->store('destination','public');
            // $image = Image::make(public_path("storage/{$imagePath}"))->fit(1000,1000);
            // $image->save();
            $imageArray = ['image'=> $imagePath ];
        }

        $destination->update(array_merge(
            $data,
            $imageArray ?? []
        ));

        return $destination;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $destination = Destination::find($id);

        $destination->delete();

        return true;
    }
}
