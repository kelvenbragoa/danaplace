<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnergyInvoice;
use App\Models\EnergyInvoiceItem;
use App\Models\EnergyReading;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EnergyInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $searchQuery = request('query');
        $status = request('status');
        $month = request('month');
        $year = request('year');

        $invoices = EnergyInvoice::query()
            ->when($searchQuery, function($query, $searchQuery) {
                $query->where('id', 'like', "%{$searchQuery}%")
                      ->orWhereHas('energyInvoiceItems.equipment', function($q) use ($searchQuery) {
                          $q->where('name', 'like', "%{$searchQuery}%");
                      });
            })
            ->when($status, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($month, function($query, $month) {
                $query->whereMonth('start_date_period', $month);
            })
            ->when($year, function($query, $year) {
                $query->whereYear('start_date_period', $year);
            })
            ->with(['energyInvoiceItems.equipment.destination'])
            ->withCount(['energyInvoiceItems'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Adicionar contagens de itens pagos/não pagos
        foreach ($invoices as $invoice) {
            // Calcular valores dinâmicos (não salvos no banco)
            $paidItemsCount = $invoice->energyInvoiceItems->where('is_paid', true)->count();
            $unpaidItemsCount = $invoice->energyInvoiceItems->where('is_paid', false)->count();
            $paidAmount = $invoice->energyInvoiceItems->where('is_paid', true)->sum('total_to_invoice');
            $remainingAmount = $invoice->energyInvoiceItems->where('is_paid', false)->sum('total_to_invoice');
            
            // Adicionar como propriedades dinâmicas (não persistentes)
            $invoice->paid_items_count = $paidItemsCount;
            $invoice->unpaid_items_count = $unpaidItemsCount;
            $invoice->paid_amount = $paidAmount;
            $invoice->remaining_amount = $remainingAmount;
            //este total amount irei modificar para pegar dos itens da fatura, algum erro faz com que o valor total acoplado na fatura fique errado
            // $invoice->total_amount = $invoice->total_amount;
            $invoice->total_amount = $invoice->energyInvoiceItems->sum('total_to_invoice');
            
            // Calcular e atualizar apenas o status (campo que existe na tabela) // agora irei retirar 
            $totalItems = $invoice->energyInvoiceItems->count();
            $currentStatus = $invoice->status;
            
            if ($paidItemsCount == 0) {
                $newStatus = 'issued';
            } elseif ($paidItemsCount == $totalItems) {
                $newStatus = 'paid';
            } else {
                $newStatus = 'partially_paid';
            }
            
            // Só salvar se o status mudou
            if ($currentStatus !== $newStatus) {
                $invoice->status = $newStatus;
                $invoice->saveQuietly(); // Evita eventos desnecessários
            }
        }

        // Estatísticas para dashboard
        $statistics = [
            'total_invoices' => EnergyInvoice::count(),
            'total_amount' => EnergyInvoice::sum('invoice_total_cost'),
            'paid_invoices' => EnergyInvoice::where('status', 'paid')->count(),
            'pending_invoices' => EnergyInvoice::where('status', 'issued')->count(),
            'paid_amount' => EnergyInvoiceItem::where('is_paid', true)->sum('total_to_invoice'),
            'pending_amount' => EnergyInvoiceItem::where('is_paid', false)->sum('total_to_invoice')
        ];

        return response()->json([
            'invoices' => $invoices,
            'statistics' => $statistics
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
    // public function store(Request $request)
    // {
    //     //
    //     $data = $request->all();
    //     $total_apr_consumption = 0;
    //     $total_cost_items = 0;
    //     $total_to_invoice_items = 0;
    //     $total_value_items = 0;


    //     foreach ($data['quotation'] as $quotation2) {
    //         $total_apr_consumption +=  $quotation2['apr_consumption'] ?? 0;
    //         $total_cost_items += $quotation2['cost'] ?? 0;
            
    //     }

    //     $amount_invoice_without_iva = $data['active_energy_consumption_cost'] + $data['reactive_energy_consumption_cost'] + $data['loss_cost'] + $data['ponta_cost'] + $data['fix_rate_cost'];
    //     $reduced_value = $amount_invoice_without_iva * 0.62;
    //     $data['tax_iva'] = $reduced_value  * 0.16;
    //     $data['invoice_total_cost'] = $amount_invoice_without_iva + $data['tax_iva'];

    //     $diferrence = ($data['invoice_total_cost'] - $total_cost_items)/count($data['quotation']);

    //     $energyinvoice = EnergyInvoice::create([
            
    //         'start_date_period'=>$data['start_date_period'],
    //         'end_date_period'=>$data['end_date_period'],
    //         'active_energy_consumption'=>$data['active_energy_consumption'],
    //         'active_energy_consumption_cost'=>$data['active_energy_consumption_cost'],
    //         'reactive_energy_consumption'=>$data['reactive_energy_consumption'],
    //         'reactive_energy_consumption_cost'=>$data['reactive_energy_consumption_cost'],
    //         'loss'=>$data['loss'],
    //         'loss_cost'=>$data['loss_cost'],
    //         'ponta'=>$data['ponta'],
    //         'ponta_cost'=>$data['ponta_cost'],
    //         'fix_rate'=>$data['fix_rate'],
    //         'fix_rate_cost'=>$data['fix_rate_cost'],
    //         'tax_iva'=>$data['tax_iva'],
    //         'invoice_total_cost'=>$data['invoice_total_cost'],

    //         'total_apr_consumption'=>$total_apr_consumption,
    //         'total_cost_items'=>$total_cost_items,
    //         'total_to_invoice_items'=>$total_cost_items,


    //     ]);

    //     foreach ($data['quotation'] as $quotation2) {
    //         $equipment = Equipment::find($quotation2['equipment_id']);
    //         $reduced_value_item = $quotation2['cost'] * 0.62;
    //         $iva_item = $reduced_value_item * 0.16;
    //         $total_item = $quotation2['cost'] + $iva_item;
    //         $total_to_invoice = $total_item + $diferrence;

    //         $total_to_invoice_items = $total_to_invoice_items + $total_to_invoice;
    //         $total_value_items = $total_value_items + $total_item;

    //         EnergyInvoiceItem::create([
    //             'energy_invoice_id'=>$energyinvoice->id,
    //             'equipment_id'=>$quotation2['equipment_id'],
    //             'destination_id'=>$equipment->destination_id,
    //             'apr_consumption'=>$quotation2['apr_consumption'] ?? 0,
    //             'cost'=>$quotation2['cost'] ?? 0,
    //             'percentage_value'=>$reduced_value_item,
    //             'tax_iva'=> $iva_item,
    //             'total'=>$total_item,
    //             'total_to_invoice'=>$total_to_invoice
    //         ]);
    //     }

    //     $energyinvoice->update([
    //         'total_to_invoice_items'=>$total_to_invoice_items,
    //         'total_value_items'=>$total_value_items,
    //     ]);

    //     $updatedEnergyInvoice = EnergyInvoice::with('energy_invoice_items')->find($energyinvoice->id);
    //     $updatedEnergyInvoiceItem = EnergyInvoiceItem::where('energy_invoice_id', $updatedEnergyInvoice->id)->get();



    //     foreach ($updatedEnergyInvoiceItem as $item) {
    //         $total_to_invoice_updated = ($updatedEnergyInvoice->invoice_total_cost-$updatedEnergyInvoice->invoice_total_cost)/ count($updatedEnergyInvoiceItem);
    //         $item->update([
    //             'total_to_invoice'=> $total_to_invoice_updated
    //         ]);
    //     }


    //     return [
    //         'message'=>'success'
    //     ];
    // }

    public function store(Request $request)
{
    // 1️⃣ Validação de dados recebidos
    $validated = $request->validate([
        'start_date_period' => 'required|date',
        'end_date_period' => 'required|date',
        'active_energy_consumption' => 'required|numeric',
        'active_energy_consumption_cost' => 'required|numeric',
        'reactive_energy_consumption' => 'required|numeric',
        'reactive_energy_consumption_cost' => 'required|numeric',
        'loss' => 'required|numeric',
        'loss_cost' => 'required|numeric',
        'ponta' => 'required|numeric',
        'ponta_cost' => 'required|numeric',
        'fix_rate' => 'required|numeric',
        'fix_rate_cost' => 'required|numeric',
        'quotation' => 'required|array',
        'quotation.*.equipment_id' => 'required|integer|exists:equipment,id',
        'quotation.*.apr_consumption' => 'nullable|numeric',
        // 'quotation.*.cost' => 'required|numeric',
    ]);

    // 2️⃣ Cálculo dos totais iniciais
    $totalAprConsumption = 0;
    $totalCostItems = 0;

    // 5️⃣ Criar itens da fatura
    $totalValueItems = 0;
    $totalToInvoiceItems = 0;

    //calculo ponta_plus_fix_rate
    $pontaPlusFixRate = $validated['ponta_cost'] + $validated['fix_rate_cost'];
    //calculo fix_rate_plus_fix_rate_per_house
    $fixRatePlusFixRatePerHouse = $pontaPlusFixRate / count($validated['quotation']);   
    //calculo rate_per_active_consumption
    $ratePerActiveConsumption = ($validated['active_energy_consumption_cost'] + $validated['loss_cost']) / $validated['active_energy_consumption'];

    foreach ($validated['quotation'] as $quotationItem) {
        $costSum = $ratePerActiveConsumption * $quotationItem['apr_consumption'] + $fixRatePlusFixRatePerHouse;
        $totalAprConsumption += $quotationItem['apr_consumption'] ?? 0;
        
        $totalCostItems += $costSum ?? 0;


        // calculo
        $reducedValueItem = $costSum * 0.62;
        $ivaItem = $reducedValueItem * 0.16;
        $totalItem = $costSum + $ivaItem;
        // $totalToInvoice = $totalItem + $difference;

        $totalValueItems += $totalItem;
        // $totalToInvoiceItems += $totalToInvoice;
    }

    // 3️⃣ Cálculo de valores e impostos
    $amountWithoutIVA = $validated['active_energy_consumption_cost']
        + $validated['reactive_energy_consumption_cost']
        + $validated['loss_cost']
        + $validated['ponta_cost']
        + $validated['fix_rate_cost'];

    $reducedValue = $amountWithoutIVA * 0.62;
    $taxIVA = $reducedValue * 0.16;
    $invoiceTotalCost = $amountWithoutIVA + $taxIVA;

    // Diferença a ser distribuída entre os itens
    $difference = count($validated['quotation']) > 0
        ? ($invoiceTotalCost - $totalValueItems) / count($validated['quotation'])
        : 0;

    


    // 4️⃣ Criar a fatura (EnergyInvoice)
    $energyInvoice = EnergyInvoice::create([
        'start_date_period' => $validated['start_date_period'],
        'end_date_period' => $validated['end_date_period'],
        'active_energy_consumption' => $validated['active_energy_consumption'],
        'active_energy_consumption_cost' => $validated['active_energy_consumption_cost'],
        'reactive_energy_consumption' => $validated['reactive_energy_consumption'],
        'reactive_energy_consumption_cost' => $validated['reactive_energy_consumption_cost'],
        'loss' => $validated['loss'],
        'loss_cost' => $validated['loss_cost'],
        'ponta' => $validated['ponta'],
        'ponta_cost' => $validated['ponta_cost'],
        'fix_rate' => $validated['fix_rate'],
        'fix_rate_cost' => $validated['fix_rate_cost'],
        'tax_iva' => $taxIVA,
        'invoice_total_cost' => $invoiceTotalCost,
        'total_apr_consumption' => $totalAprConsumption,
        'total_cost_items' => $totalCostItems,
        'total_to_invoice_items' => $totalCostItems,
        'difference' => $difference,
        'quantity_houses' => count($validated['quotation']),
        'ponta_plus_fix_rate' => $pontaPlusFixRate,
        'fix_rate_plus_fix_rate_per_house' => $fixRatePlusFixRatePerHouse,
        'rate_per_active_consumption' => $ratePerActiveConsumption,
    ]);



   

    foreach ($validated['quotation'] as $quotationItem) {
        //calculo de cost
        $cost = $ratePerActiveConsumption * $quotationItem['apr_consumption'] + $fixRatePlusFixRatePerHouse;
        
        $equipment = Equipment::findOrFail($quotationItem['equipment_id']);
        $reducedValueItem = $cost * 0.62;
        $ivaItem = $reducedValueItem * 0.16;
        $totalItem = $cost + $ivaItem;
        $totalToInvoice = $totalItem + $difference;

        $totalValueItems += $totalItem;
        $totalToInvoiceItems += $totalToInvoice;

        

        EnergyInvoiceItem::create([
            'energy_invoice_id' => $energyInvoice->id,
            'equipment_id' => $quotationItem['equipment_id'],
            'destination_id' => $equipment->destination_id,
            'apr_consumption' => $quotationItem['apr_consumption'] ?? 0,
            'cost' => $cost,
            'percentage_value' => $reducedValueItem,
            'tax_iva' => $ivaItem,
            'total' => $totalItem,
            'total_to_invoice' => $totalToInvoice,
        ]);
    }

    // 6️⃣ Atualizar a fatura com os totais corretos
    $total_to_invoice_updated = EnergyInvoiceItem::where('energy_invoice_id', $energyInvoice->id)->sum('total_to_invoice');
    $total_value_updated = EnergyInvoiceItem::where('energy_invoice_id', $energyInvoice->id)->sum('total');

    $energyInvoice->update([
        'total_value_items' => $total_value_updated,
        'total_to_invoice_items' => $total_to_invoice_updated,
    ]);
    

    // 7️⃣ Atualizar individualmente o total_to_invoice de cada item
    $items = $energyInvoice->energy_invoice_items;

    if ($items->count() > 0) {
        $diffPerItem = ($energyInvoice->invoice_total_cost - $energyInvoice->total_value_items) / $items->count();

        foreach ($items as $item) {
            $item->update([
                'total_to_invoice' => $item->total + $diffPerItem,
            ]);
        }
    }

    

    // dd('Energy invoice created successfully.');

    // 8️⃣ Retorno de sucesso
    return response()->json([
        'message' => 'Energy invoice created successfully.',
        'data' => $energyInvoice->load('energy_invoice_items'),
    ]);
}

    /**
     * Display the specified resource.
     */
    public function showClient(string $id){
        //
        $energyinvoice = EnergyInvoiceItem::
        with('equipment')
        ->with('destination')
        ->with('energyinvoice')
        ->find($id);

        return [
            'energyinvoiceitem'=>$energyinvoice,
        ];
    }
    public function show(string $id)
    {
        $invoice = EnergyInvoice::with([
            'energyInvoiceItems.equipment.destination',
            'energyInvoiceItems.markedByUser'
        ])->findOrFail($id);

        // Calcular valores de pagamento
        $invoice->paid_amount = $invoice->energyInvoiceItems->where('is_paid', true)->sum('total_to_invoice');
        $invoice->remaining_amount = $invoice->energyInvoiceItems->where('is_paid', false)->sum('total_to_invoice');
        $invoice->total_amount = $invoice->energyInvoiceItems->sum('total_to_invoice');

        // Atualizar status
        $totalItems = $invoice->energyInvoiceItems->count();
        $paidItems = $invoice->energyInvoiceItems->where('is_paid', true)->count();
        
        if ($paidItems == 0) {
            $invoice->status = 'issued';
        } elseif ($paidItems == $totalItems) {
            $invoice->status = 'paid';
        } else {
            $invoice->status = 'partially_paid';
        }

        return response()->json([
            'energyinvoice' => $invoice,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $energyinvoice = EnergyInvoice::
        with('energy_invoice_items.equipment')
        ->with('energy_invoice_items.destination')
        ->find($id);
        return response()->json([
            'invoice'=>$energyinvoice
            ]);
    }

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, $id)
{
    // 1️⃣ Buscar a fatura existente
    $energyInvoice = EnergyInvoice::findOrFail($id);

    // 2️⃣ Validação
    $validated = $request->validate([
        'start_date_period' => 'required|date',
        'end_date_period' => 'required|date',
        'active_energy_consumption' => 'required|numeric',
        'active_energy_consumption_cost' => 'required|numeric',
        'reactive_energy_consumption' => 'required|numeric',
        'reactive_energy_consumption_cost' => 'required|numeric',
        'loss' => 'required|numeric',
        'loss_cost' => 'required|numeric',
        'ponta' => 'required|numeric',
        'ponta_cost' => 'required|numeric',
        'fix_rate' => 'required|numeric',
        'fix_rate_cost' => 'required|numeric',
        'quotation' => 'required|array',
        'quotation.*.equipment_id' => 'required',
        'quotation.*.apr_consumption' => 'required',
    ]);

    // 3️⃣ Recalcular valores
    $totalAprConsumption = 0;
    $totalCostItems = 0;
    $totalValueItems = 0;
    $totalToInvoiceItems = 0;

    $pontaPlusFixRate = $validated['ponta_cost'] + $validated['fix_rate_cost'];
    $fixRatePlusFixRatePerHouse = $pontaPlusFixRate / count($validated['quotation']);   
    $ratePerActiveConsumption = ($validated['active_energy_consumption_cost'] + $validated['loss_cost']) / $validated['active_energy_consumption'];

    foreach ($validated['quotation'] as $quotationItem) {
        $costSum = $ratePerActiveConsumption * $quotationItem['apr_consumption'] + $fixRatePlusFixRatePerHouse;
        $totalAprConsumption += $quotationItem['apr_consumption'] ?? 0;
        $totalCostItems += $costSum ?? 0;

        $reducedValueItem = $costSum * 0.62;
        $ivaItem = $reducedValueItem * 0.16;
        $totalItem = $costSum + $ivaItem;

        $totalValueItems += $totalItem;
    }

    $amountWithoutIVA = $validated['active_energy_consumption_cost']
        + $validated['reactive_energy_consumption_cost']
        + $validated['loss_cost']
        + $validated['ponta_cost']
        + $validated['fix_rate_cost'];

    $reducedValue = $amountWithoutIVA * 0.62;
    $taxIVA = $reducedValue * 0.16;
    $invoiceTotalCost = $amountWithoutIVA + $taxIVA;

    $difference = count($validated['quotation']) > 0
        ? ($invoiceTotalCost - $totalValueItems) / count($validated['quotation'])
        : 0;

    // 4️⃣ Atualizar a fatura
    $energyInvoice->update([
        'start_date_period' => $validated['start_date_period'],
        'end_date_period' => $validated['end_date_period'],
        'active_energy_consumption' => $validated['active_energy_consumption'],
        'active_energy_consumption_cost' => $validated['active_energy_consumption_cost'],
        'reactive_energy_consumption' => $validated['reactive_energy_consumption'],
        'reactive_energy_consumption_cost' => $validated['reactive_energy_consumption_cost'],
        'loss' => $validated['loss'],
        'loss_cost' => $validated['loss_cost'],
        'ponta' => $validated['ponta'],
        'ponta_cost' => $validated['ponta_cost'],
        'fix_rate' => $validated['fix_rate'],
        'fix_rate_cost' => $validated['fix_rate_cost'],
        'tax_iva' => $taxIVA,
        'invoice_total_cost' => $invoiceTotalCost,
        'total_apr_consumption' => $totalAprConsumption,
        'total_cost_items' => $totalCostItems,
        'total_to_invoice_items' => $totalCostItems,
        'difference' => $difference,
        'quantity_houses' => count($validated['quotation']),
        'ponta_plus_fix_rate' => $pontaPlusFixRate,
        'fix_rate_plus_fix_rate_per_house' => $fixRatePlusFixRatePerHouse,
        'rate_per_active_consumption' => $ratePerActiveConsumption,
    ]);

    // 5️⃣ Apagar itens antigos
    $energyInvoice->energy_invoice_items()->delete();

    // 6️⃣ Criar itens novamente
    foreach ($validated['quotation'] as $quotationItem) {
        $cost = $ratePerActiveConsumption * $quotationItem['apr_consumption'] + $fixRatePlusFixRatePerHouse;
        $equipment = Equipment::findOrFail($quotationItem['equipment_id']);
        $reducedValueItem = $cost * 0.62;
        $ivaItem = $reducedValueItem * 0.16;
        $totalItem = $cost + $ivaItem;
        $totalToInvoice = $totalItem + $difference;

        EnergyInvoiceItem::create([
            'energy_invoice_id' => $energyInvoice->id,
            'equipment_id' => $quotationItem['equipment_id'],
            'destination_id' => $equipment->destination_id,
            'apr_consumption' => $quotationItem['apr_consumption'] ?? 0,
            'cost' => $cost,
            'percentage_value' => $reducedValueItem,
            'tax_iva' => $ivaItem,
            'total' => $totalItem,
            'total_to_invoice' => $totalToInvoice,
        ]);
    }

    // 7️⃣ Atualizar totais finais
    $total_to_invoice_updated = EnergyInvoiceItem::where('energy_invoice_id', $energyInvoice->id)->sum('total_to_invoice');
    $total_value_updated = EnergyInvoiceItem::where('energy_invoice_id', $energyInvoice->id)->sum('total');

    $energyInvoice->update([
        'total_value_items' => $total_value_updated,
        'total_to_invoice_items' => $total_to_invoice_updated,
    ]);

    // 8️⃣ Ajustar diferença final
    $items = $energyInvoice->energy_invoice_items;
    if ($items->count() > 0) {
        $diffPerItem = ($energyInvoice->invoice_total_cost - $energyInvoice->total_value_items) / $items->count();
        foreach ($items as $item) {
            $item->update([
                'total_to_invoice' => $item->total + $diffPerItem,
            ]);
        }
    }

    return response()->json([
        'message' => 'Energy invoice updated successfully.',
        'data' => $energyInvoice->load('energy_invoice_items'),
    ]);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $energyInvoice = EnergyInvoice::findOrFail($id);
            $energyInvoice->delete();
            
            return response()->json([
                'message' => 'Fatura de energia excluída com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao excluir fatura de energia'
            ], 500);
        }
    }

    /**
     * Toggle payment status of an energy invoice item.
     */
    public function toggleItemPayment(Request $request, $invoiceId, $itemId)
    {
        $request->validate([
            'is_paid' => 'required|boolean',
            'payment_details' => 'nullable|array'
        ]);

        try {
            $invoice = EnergyInvoice::findOrFail($invoiceId);
            $item = EnergyInvoiceItem::where('energy_invoice_id', $invoiceId)
                                   ->where('id', $itemId)
                                   ->firstOrFail();

            // Atualizar o status de pagamento
            $item->is_paid = $request->is_paid;
            if ($request->is_paid) {
                $item->paid_at = now();
                $item->marked_by = auth()->id();
                $item->payment_details = $request->payment_details ?? [];
            } else {
                $item->paid_at = null;
                $item->marked_by = null;
                $item->payment_details = null;
            }
            $item->save();

            // Recalcular valores da fatura
            $invoice = $invoice->fresh(['energyInvoiceItems']);
            $invoice->paid_amount = $invoice->energyInvoiceItems->where('is_paid', true)->sum('total_to_invoice');
            $invoice->remaining_amount = $invoice->energyInvoiceItems->where('is_paid', false)->sum('total_to_invoice');
            $invoice->total_amount = $invoice->invoice_total_cost;

            // Atualizar status da fatura
            $totalItems = $invoice->energyInvoiceItems->count();
            $paidItems = $invoice->energyInvoiceItems->where('is_paid', true)->count();
            
            if ($paidItems == 0) {
                $invoice->status = 'issued';
            } elseif ($paidItems == $totalItems) {
                $invoice->status = 'paid';
            } else {
                $invoice->status = 'partially_paid';
            }
            
            $invoice->save();

            return response()->json([
                'message' => $request->is_paid ? 'Item marcado como pago!' : 'Item marcado como não pago!',
                'item' => $item->fresh()->load('equipment'),
                'invoice' => $invoice->fresh(['energyInvoiceItems'])
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao atualizar pagamento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Dashboard de estatísticas
     */
    public function dashboard()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $stats = [
            'current_month' => [
                'invoices' => EnergyInvoice::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count(),
                'total_amount' => EnergyInvoice::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('invoice_total_cost'),
                'paid_amount' => EnergyInvoiceItem::whereHas('energyInvoice', function($q) use ($currentMonth, $currentYear) {
                    $q->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear);
                })->where('is_paid', true)->sum('total_to_invoice'),
            ],
            'monthly_stats' => EnergyInvoice::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as count, SUM(invoice_total_cost) as total')
                                           ->whereYear('created_at', $currentYear)
                                           ->groupBy(DB::raw('MONTH(created_at), YEAR(created_at)'))
                                           ->orderBy(DB::raw('MONTH(created_at)'))
                                           ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Listar leituras de uma fatura específica
     */
    public function getReadings($invoiceId)
    {
        try {
            $invoice = EnergyInvoice::findOrFail($invoiceId);
            
            $readings = EnergyReading::with(['equipment', 'technician'])
                ->where('energy_invoice_id', $invoiceId)
                ->orderBy('reading_date', 'desc')
                ->get();

            // Obter equipamentos disponíveis para esta fatura
            $equipments = Equipment::whereIn('id', function($query) use ($invoiceId) {
                $query->select('equipment_id')
                      ->from('energy_invoice_items')
                      ->where('energy_invoice_id', $invoiceId);
            })->get();

            // Obter técnicos disponíveis
            $technicians = User::where('role_id', 1)->get();

            return response()->json([
                'readings' => $readings,
                'equipments' => $equipments,
                'technicians' => $technicians,
                'invoice' => $invoice
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao carregar leituras',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar nova leitura
     */
    public function storeReading(Request $request, $invoiceId)
    {
        try {
            $invoice = EnergyInvoice::findOrFail($invoiceId);

            $validated = $request->validate([
                'equipment_id' => 'required|exists:equipment,id',
                'reading_date' => 'required|date',
                'reading_value' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:500'
            ]);

            // Verificar se já existe leitura para este equipamento nesta data
            $existingReading = EnergyReading::where('equipment_id', $validated['equipment_id'])
                ->where('reading_date', $validated['reading_date'])
                ->first();

            if ($existingReading) {
                return response()->json([
                    'message' => 'Já existe uma leitura para este equipamento nesta data'
                ], 422);
            }

            // Buscar leitura anterior
            $previousReading = EnergyReading::getPreviousReading(
                $validated['equipment_id'], 
                $validated['reading_date']
            );

            $readingData = array_merge($validated, [
                'energy_invoice_id' => $invoiceId,
                'user_id' => Auth::id(),
                'previous_reading' => $previousReading ? $previousReading->reading_value : null
            ]);

            $reading = EnergyReading::create($readingData);
            $reading->load(['equipment', 'technician']);

            return response()->json([
                'message' => 'Leitura registrada com sucesso',
                'reading' => $reading
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao registrar leitura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar leitura
     */
    public function updateReading(Request $request, $invoiceId, $readingId)
    {
        try {
            $invoice = EnergyInvoice::findOrFail($invoiceId);
            $reading = EnergyReading::where('energy_invoice_id', $invoiceId)
                ->findOrFail($readingId);

            $validated = $request->validate([
                'reading_date' => 'required|date',
                'reading_value' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:500'
            ]);

            // Verificar se já existe outra leitura para este equipamento nesta data
            $existingReading = EnergyReading::where('equipment_id', $reading->equipment_id)
                ->where('reading_date', $validated['reading_date'])
                ->where('id', '!=', $readingId)
                ->first();

            if ($existingReading) {
                return response()->json([
                    'message' => 'Já existe uma leitura para este equipamento nesta data'
                ], 422);
            }

            // Se a data mudou, recalcular leitura anterior
            if ($reading->reading_date != $validated['reading_date']) {
                $previousReading = EnergyReading::getPreviousReading(
                    $reading->equipment_id, 
                    $validated['reading_date']
                );
                $validated['previous_reading'] = $previousReading ? $previousReading->reading_value : null;
            }

            $reading->update($validated);
            $reading->load(['equipment', 'technician']);

            return response()->json([
                'message' => 'Leitura atualizada com sucesso',
                'reading' => $reading
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar leitura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar leitura
     */
    public function destroyReading($invoiceId, $readingId)
    {
        try {
            $invoice = EnergyInvoice::findOrFail($invoiceId);
            $reading = EnergyReading::where('energy_invoice_id', $invoiceId)
                ->findOrFail($readingId);

            $reading->delete();

            return response()->json([
                'message' => 'Leitura excluída com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao excluir leitura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas das leituras
     */
    public function getReadingStats($invoiceId)
    {
        try {
            $invoice = EnergyInvoice::findOrFail($invoiceId);
            
            $stats = [
                'total_readings' => EnergyReading::where('energy_invoice_id', $invoiceId)->count(),
                'equipments_with_readings' => EnergyReading::where('energy_invoice_id', $invoiceId)->distinct('equipment_id')->count(),
                'total_consumption' => EnergyReading::where('energy_invoice_id', $invoiceId)->sum('consumption'),
                'readings_by_date' => EnergyReading::where('energy_invoice_id', $invoiceId)
                    ->selectRaw('reading_date, COUNT(*) as count, SUM(consumption) as total_consumption')
                    ->groupBy('reading_date')
                    ->orderBy('reading_date')
                    ->get(),
                'readings_by_equipment' => EnergyReading::with('equipment')
                    ->where('energy_invoice_id', $invoiceId)
                    ->selectRaw('equipment_id, COUNT(*) as count, SUM(consumption) as total_consumption, AVG(consumption) as avg_consumption')
                    ->groupBy('equipment_id')
                    ->get()
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao carregar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
