<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Equipment;
use App\Models\Mcscr;
use App\Models\Province;
use App\Models\User;
use Illuminate\Http\Request;

class AreasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $areas = Area::query()
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

            return $areas;
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
        $area = Area::create($data);
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
        $area = Area::
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
        ->where('area_id',$id)
        ->orderBy('name','asc')
        ->paginate();


        return [
            'area'=>$area,
            'equipments'=>$equipments
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $area = Area::find($id);
        $province = Province::orderBy('name','asc')->get();
        


        return [
            'area'=>$area,
            'provinces'=>$province
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $area = Area::find($id);

        $area->update($data);

        return $area;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $area = Area::find($id);

        $msg = 'Não foi possivel apagar a área.Existem : ';

        $mcscr = Mcscr::where('area_id',$id)->get();
        $equipment = Equipment::where('area_id',$id)->get();
        $user = User::where('area_id',$id)->get();


        if($mcscr->count()>0){
            $msg = $msg.' MCSCR ';
        }
        if($equipment->count()>0){
            $msg = $msg.' Equipamentos ';
        }
        if($user->count()>0){
            $msg = $msg.' Usuários ';
        }

        $msg = $msg.' alocados a esta área. ';

        if($mcscr->count()>0 || $equipment->count()>0 || $user->count()>0){

            return response()->json([
                'message' => $msg
            ], 404);

        }else{

            $area->delete();

            return true;
        }
        
    }
}
