<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CenterCost;
use App\Models\CenterCostAccount;
use App\Models\Equipment;
use Illuminate\Http\Request;

class CenterCostAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

        $centercostaccounts = CenterCostAccount::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%")->orWhere('code','like',"%{$searchQuery}%");
        })
        ->with('equipments')
        ->orderBy('name','asc')
        ->paginate();

        return $centercostaccounts;
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
        $centercost = CenterCostAccount::create($data);
        $centercost2 = CenterCost::with('accounts')->with('equipments')->find($data['center_cost_id']);

        $accounts = CenterCostAccount::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('name','like',"%{$searchQuery}%")->orWhere('code','like',"%{$searchQuery}%");
        })
        ->with('center_cost')
        ->with('equipments')
        ->where('center_cost_id',$data['center_cost_id'])
        ->orderBy('name','asc')
        ->paginate();

        return [
            'centercost'=>$centercost2,
            'accounts'=>$accounts
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $account = CenterCostAccount::with('center_cost')->with('equipments')->find($id);

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
        ->where('center_cost_account_id',$id)
        ->orderBy('name','asc')
        ->paginate();

        return [
            'account' => $account,
            'equipments'=>$equipments
        
        ];
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $account = CenterCostAccount::find($id);

        return $account;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $account = CenterCostAccount::find($id);

        $data = $request->all();

        $account->update($data);

        return $account;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $account = CenterCostAccount::find($id);

        $account->delete();

        return true;
    }
}
