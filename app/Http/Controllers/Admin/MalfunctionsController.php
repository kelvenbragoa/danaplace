<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mcscr;
use App\Models\TypeMalfunction;
use Illuminate\Http\Request;

class MalfunctionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $malfunctions = TypeMalfunction::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('mcscr')
            ->with('mcscr_done')
            ->with('mcscr_not_done')
            ->with('mcscr_approval')
            ->orderBy('name','asc')
            ->paginate();

            return $malfunctions;
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
        $malfunction = TypeMalfunction::create($data);
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
        $malfunction = TypeMalfunction::
        with('mcscr')
        ->with('mcscr_done')
        ->with('mcscr_not_done')
        ->with('mcscr_approval')
        ->find($id);

        $searchQuery = request('query');

        $mcscrs = Mcscr::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('reason','like',"%{$searchQuery}%")->orWhere('cause','like',"%{$searchQuery}%")->orWhere('consequence','like',"%{$searchQuery}%")->orWhere('solution','like',"%{$searchQuery}%")->orWhere('recommendation','like',"%{$searchQuery}%");
        })
        ->with('mcscr_status')
        ->with('equipment')
        ->with('reason_name')
        ->with('solution_name')
        ->with('consequence_name')
        ->with('recommendation_name')
        ->with('consequence_name')
        ->where('type_malfunction_id',$id)
        ->orderBy('id','desc')
        ->paginate();


        $dataChartMalfunctions = [];
        $dataChartAverageMalfunctions = [];

        for($i=1; $i<=12; $i++){
            $mcscr = Mcscr::whereMonth('opened_at',$i)->where('type_malfunction_id',$id)->whereYear('opened_at',date('Y'))->get();
            

            $dataChartMalfunctions[]=$mcscr->count();
            $dataChartAverageMalfunctions[]=$mcscr->avg('total_hours');
           

           
        }

        

        return [
            'malfunctions'=>$malfunction,
            'mcscrs'=>$mcscrs,
            'dataChartAverageMalfunctions'=>$dataChartAverageMalfunctions,
            'dataChartMalfunctions'=>$dataChartMalfunctions,

        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $malfunction = TypeMalfunction::find($id);
        


        return $malfunction;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $malfunction = TypeMalfunction::find($id);

        $malfunction->update($data);

        return $malfunction;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $malfunction = TypeMalfunction::find($id);

        $malfunction->delete();

        return true;
    }
}
