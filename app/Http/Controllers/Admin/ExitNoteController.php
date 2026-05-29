<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExitNote;
use App\Models\ExitNoteItem;
use App\Models\Product;
use App\Models\StockCenterProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExitNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $exitnotes = ExitNote::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('ref','like',"%{$searchQuery}%");
            })
            ->with('stockcenter')
            ->with('supplier')
            ->orderBy('created_at','desc')
            ->paginate();

            return $exitnotes;
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

        
        $exitnote = ExitNote::create([
            'user_id'=>Auth::user()->id,
            'ref'=>$data['ref'],
            'document_ref'=>$data['document_ref'],
            'serie'=>$data['serie'],
            'stock_supplier_id'=>$data['stock_supplier_id'],
            'stock_center_id'=>$data['stock_center_id'],
            'products_number'=>count($data['stockcenterproducts']),
        ]);

        foreach($data['stockcenterproducts'] as $item){

            $stockcenterproduct = StockCenterProduct::find($item['id']);
            $last_quantity = $stockcenterproduct->quantity;
            $product = Product::find($item['product_id']);

            if($data['stock_center_id'] == 1){

                $product->update([
                    'quantity'=>$last_quantity - $item['quantity']
                ]);
            }

            $stockcenterproduct->update([
                'quantity'=>$last_quantity - $item['quantity']
            ]);

            $exitNoteItem = ExitNoteItem::create([
                'stock_center_id'=>$data['stock_center_id'],
                'exit_note_id'=>$exitnote->id,
                'product_id'=>$item['product_id'],
                'quantity'=>$item['quantity'],
                'last_quantity'=>$last_quantity
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
        $exitnote = ExitNote::
        with('stockcenter')
        ->with('user')
        ->with('supplier')
        ->with('itens.product')
        ->find($id);

        return [
            'exitnote'=>$exitnote,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $exitnote = ExitNote::find($id);
        


        return $exitnote;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $exitnote = ExitNote::find($id);

        $exitnote->update($data);

        return $exitnote;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $exitnote = ExitNote::find($id);

        $exitnote->delete();

        return true;
    }
}
