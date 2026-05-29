<?php

namespace App\Http\Controllers\Destination;

use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $quotations = Quotation::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('destination')
            ->with('status')
            ->where('destination_id',Auth::user()->destination_id)
            ->paginate();

            return $quotations;
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
        // dd($data);
        $quotation = Quotation::create([
            'expires_date'=>$data['expires_date'],
            'total_amount'=>0,
            'total_discount'=>0,
            'coin_id'=>3,
            'obs'=>$data['obs'],
            'destination_id'=>Auth::user()->destination_id,
            'status_quotation_id'=>1,
            'created_by_user_id'=>Auth::user()->id
        ]);

        foreach ($data['quotation'] as $item){
            QuotationItem::create([
                'quotation_id'=>$quotation->id,
                'product_name'=>$item['product_name'],
                'quantity'=>$item['product_quantity'],
                // 'unit_price'=>$item['unit_price'],
                // 'total'=>$item['product_quantity']*$item['unit_price'],
            ]);
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
        $quotation = Quotation::with('destination.province')->with('coin')->with('itens')->find($id);
        $coins = Coin::orderBy('name','asc')->get();


        return $quotation;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $quotation = Quotation::find($id);
        


        return $quotation;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $quotation = Quotation::find($id);

        $quotation->update($data);

        return $quotation;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $quotation = Quotation::find($id);

        $quotation->delete();

        return true;
    }
}
