<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        //
        $searchQuery = request('query');

            $fees = Fee::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->orderBy('name','asc')
            ->paginate();

            return $fees;
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
        $fee = Fee::create($data);
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
        $fee = Fee::find($id);


        


        return [
            'fee'=>$fee,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $fee = Fee::find($id);
        


        return [
            'fee'=>$fee,
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $fee = Fee::find($id);

        $fee->update($data);

        return $fee;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        // $fee = Fee::find($id);

        // $msg = 'Não foi possivel apagar a área.Existem : ';

        // $mcscr = Mcscr::where('fee_id',$id)->get();
        // $equipment = Equipment::where('fee_id',$id)->get();
        // $user = User::where('fee_id',$id)->get();


        // if($mcscr->count()>0){
        //     $msg = $msg.' MCSCR ';
        // }
        // if($equipment->count()>0){
        //     $msg = $msg.' Equipamentos ';
        // }
        // if($user->count()>0){
        //     $msg = $msg.' Usuários ';
        // }

        // $msg = $msg.' alocados a esta área. ';

        // if($mcscr->count()>0 || $equipment->count()>0 || $user->count()>0){

        //     return response()->json([
        //         'message' => $msg
        //     ], 404);

        // }else{

        //     $fee->delete();

        //     return true;
        // }
        
    }
}
