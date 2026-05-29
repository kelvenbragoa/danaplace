<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockCenterProduct;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $stocktransfers = StockTransfer::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('ref','like',"%{$searchQuery}%");
            })
            ->with('stockcenterorigin')
            ->with('stockcenterdestination')
            ->orderBy('created_at','desc')
            ->paginate();

            return $stocktransfers;
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


        
        $stock_transfer = StockTransfer::create([
            'ref'=>$data['ref'],
            'stock_center_origin_id'=>$data['stock_center_origin_id'],
            'stock_center_destination_id'=>$data['stock_center_destination_id'],
        ]);

        foreach($data['stockcenterproducts'] as $item){

            //origin

            $stockcenterproduct = StockCenterProduct::find($item['id']);

            $last_quantity = $stockcenterproduct->quantity;
            $product = Product::find($item['product_id']);
            $product_last_quantity = $product->quantity;


            //stock origin
            if($data['stock_center_origin_id'] == 1){
                $product->update([
                    'quantity'=>$product_last_quantity - $item['quantity']
                ]);
            }

            $stockcenterproduct->update([
                'quantity'=> $last_quantity - $item['quantity']
            ]);

            //stock destination
            $stockcenterproductdestination = StockCenterProduct::where('stock_center_id',$data['stock_center_destination_id'])->where('product_id',$item['product_id'])->first();
            $stockcenterproductdestination_last_quantity = $stockcenterproductdestination->quantity;

            $stockcenterproductdestination->update([
                'quantity'=> $stockcenterproductdestination_last_quantity + $item['quantity']
            ]);

            $product_destination = Product::find($item['product_id']);

            if($data['stock_center_destination_id'] == 1){
                $product_destination->update([
                    'quantity'=>$stockcenterproductdestination_last_quantity + $item['quantity']
                ]);
            }

          




            $stockTransferItem = StockTransferItem::create([
                'stock_center_origin_id'=>$data['stock_center_origin_id'],
                'stock_center_destination_id'=>$data['stock_center_destination_id'],
                'stock_transfer_id'=>$stock_transfer->id,
                'product_id'=>$item['product_id'],
                'quantity'=>$item['quantity'],
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
        $stocktransfer = StockTransfer::
        with('stockcenterorigin')
        ->with('stockcenterdestination')
        ->with('itens.product')
        ->find($id);

        return [
            'stocktransfer'=>$stocktransfer,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $inventory = StockTransfer::find($id);
        


        return $inventory;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $inventory = StockTransfer::find($id);

        $inventory->update($data);

        return $inventory;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $inventory = StockTransfer::find($id);

        $inventory->delete();

        return true;
    }
}
