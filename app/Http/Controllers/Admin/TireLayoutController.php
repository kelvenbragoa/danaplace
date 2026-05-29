<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TireGeneralPosition;
use App\Models\TireLayout;
use App\Models\TireLayoutLevel;
use App\Models\TireLayoutLevelPosition;
use Illuminate\Http\Request;

class TireLayoutController extends Controller
{
    public function index()
    {
        //
        $searchQuery = request('query');

            $tirelayouts = TireLayout::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->orderBy('name','asc')
            ->paginate();

            return $tirelayouts;
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
        $index = 1;

        $tiregeneralposition = TireGeneralPosition::get();

        $tirelayout = TireLayout::create([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        foreach($data['layout'] as $layout){
            $tire_layout_level = TireLayoutLevel::create([
                'tire_layout_id'=>$tirelayout->id,
                'level'=>$index,
                'number_tires_each_side'=>$layout['tire_quantity'],
            ]);

            foreach($tiregeneralposition as $generalposition){
                for($i = 0; $i < $layout['tire_quantity']; $i++){
                    TireLayoutLevelPosition::create([
                        'tire_layout_id'=>$tirelayout->id,
                        'tire_layout_level_id'=>$tire_layout_level->id,
                        'position_id'=>$generalposition->id,
                        'position'=>$generalposition->name,
                        'name'=>$index.$generalposition->name.$i+1,
                    ]);
                }   
            }
            $index++;
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
        $tirelayout = TireLayout::find($id);
        $tirelayoutlevels = TireLayoutLevel::where('tire_layout_id',$tirelayout->id)->with('tirelayoutlevelpositions')->get();
        $tirelayoutlevelposition = TireLayoutLevelPosition::where('tire_layout_id',$tirelayout->id)->get();

        return [
            'tirelayout'=>$tirelayout,
            'tirelayoutlevels'=>$tirelayoutlevels,
            // 'tirelayoutlevelposition'=>$tirelayoutlevelposition,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $tirelayout = TireLayout::find($id);
        
        return $tirelayout;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();

        $tirelayout = TireLayout::find($id);

        $tirelayout->update($data);

        return $tirelayout;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $tirelayout = TireLayout::find($id);

        $tirelayout->delete();

        return true;
    }
}
