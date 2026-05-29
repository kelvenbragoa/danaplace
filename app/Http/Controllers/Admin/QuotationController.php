<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Destination;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\StatusQuotation;
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
                $query->where('obs','like',"%{$searchQuery}%");
            })
            ->when(request('status'),function($query,$status){
                $query->where('status_quotation_id',$status);
            })
            ->when(request('destination'),function($query,$destination){
                $query->where('destination_id',$destination);
            })
            ->with('destination')
            ->with('status')
            ->with('coin')
            ->paginate(100);

            return response()->json([
                'quotation'=>$quotations,
                'total' => Quotation::count(),
                'pendente' => Quotation::where('status_quotation_id',1)->count(),
                'aprovado' => Quotation::where('status_quotation_id',2)->count(),
                'materiaisentregue' => Quotation::where('status_quotation_id',3)->count(),
                'pagamentofeito' => Quotation::where('status_quotation_id',4)->count(),
                'foradeprazo' => Quotation::where('status_quotation_id',5)->count(),
                'processoconcluido' => Quotation::where('status_quotation_id',6)->count(),
                'materiaisemtransito' => Quotation::where('status_quotation_id',7)->count(),
                'aguardaaprovacao' => Quotation::where('status_quotation_id',8)->count(),
                'destinations'=>Destination::orderBy('name')->get()
            ]);

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
        $request->validate([
            'expires_date' => 'required|date',
            'quotation' => 'required|array',
            'quotation.*.product_name' => 'required|string',
            'quotation.*.unit_price' => 'required|numeric|min:0',
            'quotation.*.product_quantity' => 'required|integer|min:1',
            'quotation.*.discount' => 'nullable|numeric|min:0',
            'type_of_transport' => 'required|string',
            'coin_id' => 'required|integer',
            'payment_method' => 'required|string',
            'warranty' => 'nullable|string',
            'delivery_date' => 'required|date',
        ]);
    
        $data = $request->all();
        $total = 0;
        $discount = 0;
    
        foreach ($data['quotation'] as $quotation2) {
            $total += ($quotation2['unit_price'] ?? 0) * ($quotation2['product_quantity'] ?? 0);
            $discount += $quotation2['discount'] ?? 0;
        }
    
        $quotation = Quotation::create([
            'expires_date' => $data['expires_date'],
            'total_amount' => $total,
            'total_discount' => $discount,
            'obs' => $data['obs'] ?? null,
            'destination_id' => $data['destination_id'] ?? null,
            'equipment_id' => $data['equipment_id'] ?? null,
            'status_quotation_id' => 1,
            'created_by_user_id' => Auth::id(),
            'type_of_transport' => $data['type_of_transport'],
            'coin_id' => $data['coin_id'],
            'payment_method' => $data['payment_method'],
            'warranty' => $data['warranty'] ?? null,
            'delivery_date' => $data['delivery_date'],
            'representative_name' => $data['representative_name'] ?? null,
            'representative_mobile' => $data['representative_mobile'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'company_address' => $data['company_address'] ?? null,
            'company_nuit' => $data['company_nuit'] ?? null,
            'province' => $data['province'] ?? null,
            'company_mobile' => $data['company_mobile'] ?? null,
            'company_email' => $data['company_email'] ?? null,
        ]);
    
        foreach ($data['quotation'] as $item) {
            $unit_price = $item['unit_price'] ?? 0;
            $product_discount = $item['discount'] ?? 0;
            $product_quantity = $item['product_quantity'] ?? 0;
    
            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'product_name' => $item['product_name'],
                'quantity' => $product_quantity,
                'unit_price' => $unit_price,
                'discount' => $product_discount,
                'total' => $product_quantity * $unit_price,
            ]);
        }
    
        return response()->json([
            'message' => 'Quotation created successfully!',
            'quotation_id' => $quotation->id,
        ]);
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

        $quotation = Quotation::with('destination')
        ->with('status')->with('itens')->find($id);
        $coins = Coin::orderBy('name','asc')->get();
        $status = StatusQuotation::orderBy('id','asc')->get();
        


        return response()->json([
            'quotation' => $quotation,
            'coins' => $coins,
            'status' => $status
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'expires_date' => 'required|date',
            'quotation' => 'required|array',
            'quotation.*.id' => 'required|exists:quotation_items,id',
            'quotation.*.product_name' => 'required|string',
            'quotation.*.product_quantity' => 'required|integer|min:1',
            'quotation.*.unit_price' => 'required|numeric|min:0',
            'quotation.*.discount' => 'nullable|numeric|min:0',
            'status_quotation_id' => 'required|integer',
            'type_of_transport' => 'required|string',
            'coin_id' => 'required|integer',
            'payment_method' => 'required|string',
            'warranty' => 'nullable|string',
            'delivery_date' => 'required|date',
        ]);
    
        $quotation = Quotation::find($id);
        if (!$quotation) {
            return response()->json(['error' => 'Quotation not found'], 404);
        }
    
        $total = 0;
        $discount = 0;
    
        if ($request->has('quotation')) {
            foreach ($request->quotation as $quotation2) {
                $quotationitem = QuotationItem::find($quotation2['id']);
                
                $product_quantity = $quotation2['product_quantity'] ?? 0;
                $unit_price = $quotation2['unit_price'] ?? 0;
                $product_discount = $quotation2['discount'] ?? 0;
    
                $quotationitem->update([
                    'product_name' => $quotation2['product_name'],
                    'quantity' => $product_quantity,
                    'unit_price' => $unit_price,
                    'discount' => $product_discount,
                    'total' => $unit_price * $product_quantity,
                ]);
    
                $total += $unit_price * $product_quantity;
                $discount += $product_discount;
            }
        }
    
        $quotation->update([
            'expires_date' => $request->expires_date,
            'obs' => $request->obs,
            'status_quotation_id' => $request->status_quotation_id,
            'type_of_transport' => $request->type_of_transport,
            'coin_id' => $request->coin_id,
            'payment_method' => $request->payment_method,
            'warranty' => $request->warranty,
            'delivery_date' => $request->delivery_date,
            'total_amount' => $total,
            'total_discount' => $discount
        ]);
    
        return response()->json([
            'message' => 'Quotation updated successfully!',
            'quotation' => $quotation,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $quotation = Quotation::find($id);
        $quotation->itens()->delete();


        $quotation->delete();

        return true;
    }

    public function calendar(){

        $quotations = Quotation::get()->map(function($quote){
            return [
                'id'=>$quote->id,
                'title'=>'Quotação: '.$quote->destination->name.' ( '.$quote->total_amount.' ).',
                'date'=>$quote->expires_date,
                'start'=>$quote->expires_date,
                'backgroundColor'=>'#50B3C7',
                'borderColor'=>'#50B3C7',
                'color'=>'#50B3C7'
            ];
        });



        return $quotations;
    }
}
