<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CenterCost;
use App\Models\CenterCostAccount;
use Illuminate\Http\Request;

class CenterCostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

        $centercosts = CenterCost::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%")->orWhere('code','like',"%{$searchQuery}%");
        })
        ->with('accounts')
        ->with('equipments')
        ->orderBy('name','asc')
        ->paginate();

        return $centercosts;
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
        $centercost = CenterCost::create($data);
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
        $centercost = CenterCost::with('accounts')->with('equipments')->find($id);

        $searchQuery = request('query');

        $accounts = CenterCostAccount::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%")->orWhere('code','like',"%{$searchQuery}%");
        })
        ->with('center_cost')
        ->with('equipments')
        ->where('center_cost_id',$id)
        ->orderBy('name','asc')
        ->paginate();


        return [
            'centercost'=>$centercost,
            'accounts'=>$accounts
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $centercost = CenterCost::with('accounts')->find($id);
        return $centercost;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $centercost = CenterCost::find($id);
        $data = $request->all();

        $centercost->update($data);

        return $centercost;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $centercost = CenterCost::find($id);

        $centercostaccount = CenterCostAccount::where('center_cost_id',$id)->get();

        foreach($centercostaccount as $item){
            $item->delete();
        }
        $centercost->delete();

        return true;
    }
}
