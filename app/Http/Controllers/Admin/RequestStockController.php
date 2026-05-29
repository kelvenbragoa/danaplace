<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\RequestStock;
use App\Models\RequestStockItem;
use App\Models\StockCenterProduct;
use App\Models\User;
use App\Notifications\Operation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class RequestStockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

        $requeststock = RequestStock::query()
        ->when(request('query'),function($query,$searchQuery){
            $query->where('id','like',"%{$searchQuery}%");
        })
        ->with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->orderBy('id','desc')
        ->paginate();
        return $requeststock;
        
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

        if($data['type_task'] == 1){
            $requeststock = RequestStock::create([
                'mcscr_id'=>$data['request_id'],
                'first_observation'=>$data['first_observation'],
                'created_by_user_id'=>Auth::user()->id,
                'request_stock_status_id'=>1
            ]);
        }else{
            $requeststock = RequestStock::create([
                'task_mcscr_id'=>$data['request_id'],
                'first_observation'=>$data['first_observation'],
                'created_by_user_id'=>Auth::user()->id,
                'request_stock_status_id'=>1
            ]);
        }

        
    
        

        foreach ($data['materials'] as $item){
            RequestStockItem::create([
                'request_stock_id'=>$requeststock->id,
                'product_id'=>$item['product_id'],
                'stock_center_id'=>1,
                'quantity'=>$item['quantity'],
                'obs'=>$item['obs']
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
        $requeststock = RequestStock::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->find($id);

        $materials = RequestStockItem::
        where('request_stock_id',$id)
        ->with('product')
        ->orderBy('id','asc')->get();

        return [
            'requeststock'=>$requeststock,
            'materials'=>$materials
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $requeststock = RequestStock::
        with('createdbyuser')
        ->with('approvedbyuser')
        ->with('deliveredbyuser')
        ->with('status')
        ->with('mcscr')
        ->with('taskmcscr')
        ->find($id);

        $materials = RequestStockItem::
        where('request_stock_id',$id)
        ->with('product')
        ->orderBy('id','asc')->get();

        return [
            'requeststock'=>$requeststock,
            'materials'=>$materials
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();

       
        $requeststock = RequestStock::find($id);

        if($data['request_status'] == 2 || $data['request_status'] == 3){
            $requeststock->update([
                'approved_by_user_id'=>Auth::user()->id,
                'approved_date'=>now(),
                'request_stock_status_id'=>$data['request_status']
            ]);
        }
        

        if($data['request_status'] == 4){

           
            $requeststock->update([
                'delivered_by_user_id'=>Auth::user()->id,
                'delivered_date'=>now(),
                'request_stock_status_id'=>$data['request_status']
            ]);

            if($request->has('requeststockitens')){

                foreach ($data['requeststockitens'] as $item){
                    $requeststockitem = RequestStockItem::find($item['item_id']);

                    $product = Product::find($requeststockitem->product_id);

                    $stockcenterproduct = StockCenterProduct::where('product_id',$requeststockitem->product_id)->where('stock_center_id',1)->first();

                    $product->update([
                        'quantity'=>$product->quantity - $item['quantity']
                    ]);

                    $stockcenterproduct->update([
                        'quantity'=>$stockcenterproduct->quantity - $item['quantity']
                    ]);

                    if($product->quantity <= $product->stock_min){
                        //message broadcast for min stock
                        $msg = 'O produto: '.$product->name.' atingiu o seu stock mínimo de '.$product->stock_min.' '.$product->unity->name.'. A quantidade atual é de: '.$product->quantity.' '.$product->unity->name.'.';
                        $user = User::all();
                        Notification::send($user,new Operation($msg));
                    }

                    $requeststockitem->update([
                        'delivered_quantity'=>$item['quantity'],
                    ]);
                
                }
            }

        }

        
        

        return $requeststock;

    }

    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $requeststock = RequestStock::find($id);
        $requeststock->delete();
        return true;
    }
}
