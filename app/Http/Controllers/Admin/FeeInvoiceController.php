<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\Equipment;
use App\Models\Fee;
use App\Models\EquipmentFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class FeeInvoiceController extends Controller
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

        $invoices = FeeInvoice::query()
            ->when($searchQuery, function($query, $searchQuery) {
                $query->where('invoice_number', 'like', "%{$searchQuery}%")
                      ->orWhere('notes', 'like', "%{$searchQuery}%");
            })
            ->when($status, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($month, function($query, $month) {
                $query->where('month', $month);
            })
            ->when($year, function($query, $year) {
                $query->where('year', $year);
            })
            ->with(['creator', 'approver', 'items'])
            ->withCount(['items', 'paidItems', 'unpaidItems'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Estatísticas para dashboard
        $statistics = [
            'total_invoices' => FeeInvoice::count(),
            'draft_invoices' => FeeInvoice::where('status', 'draft')->count(),
            'issued_invoices' => FeeInvoice::where('status', 'issued')->count(),
            'paid_invoices' => FeeInvoice::where('status', 'paid')->count(),
            'overdue_invoices' => FeeInvoice::overdue()->count(),
            'total_amount' => FeeInvoice::sum('total_amount'),
            'paid_amount' => FeeInvoice::sum('paid_amount'),
            'pending_amount' => FeeInvoice::whereNotIn('status', ['paid', 'cancelled'])->sum('total_amount') - FeeInvoice::sum('paid_amount'),
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2050',
            'due_date' => 'required|date|after:today',
            'notes' => 'nullable|string|max:1000',
            'selected_equipments' => 'required|array|min:1',
            'selected_equipments.*' => 'integer|exists:equipment,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Verificar se já existe uma fatura para este período
            $existingInvoice = FeeInvoice::where('month', $request->month)
                                        ->where('year', $request->year)
                                        ->first();

            if ($existingInvoice) {
                return response()->json([
                    'message' => 'Já existe uma fatura para o período ' . $existingInvoice->period_description
                ], 422);
            }

            // Criar a fatura
            $invoice = new FeeInvoice([
                'month' => $request->month,
                'year' => $request->year,
                'issue_date' => now(),
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
                'status' => 'draft'
            ]);

            $invoice->invoice_number = $invoice->generateInvoiceNumber();
            $invoice->save();

            // Obter equipamentos com suas taxas
            $equipments = Equipment::whereIn('id', $request->selected_equipments)
                                  ->with('fees.fee')
                                  ->get();

            $totalAmount = 0;

            // Criar itens da fatura
            foreach ($equipments as $equipment) {
                foreach ($equipment->fees as $equipmentFee) {
                    $fee = $equipmentFee->fee;
                    if ($fee) { // Verificar se a taxa existe
                        $item = new FeeInvoiceItem([
                            'fee_invoice_id' => $invoice->id,
                            'equipment_id' => $equipment->id,
                            'fee_id' => $fee->id,
                            'amount' => $fee->amount,
                            'is_paid' => false
                        ]);
                        $item->save();
                        $totalAmount += $fee->amount;
                    }
                }
            }

            // Atualizar total da fatura
            $invoice->total_amount = $totalAmount;
            $invoice->save();

            DB::commit();

            return response()->json([
                'message' => 'Fatura criada com sucesso!',
                'invoice' => $invoice->load('items.equipment', 'items.fee')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Erro ao criar fatura: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $invoice = FeeInvoice::with([
            'creator', 
            'approver', 
            'items.equipment.destination',
            'items.equipment.type_equipment', 
            'items.fee',
            'items.markedByUser'
        ])->findOrFail($id);

        return response()->json([
            'invoice' => $invoice
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $invoice = FeeInvoice::with([
            'items.equipment.destination',
            'items.equipment.type_equipment',
            'items.fee'
        ])->findOrFail($id);

        return response()->json([
            'invoice' => $invoice
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $invoice = FeeInvoice::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'due_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $invoice->update([
                'due_date' => $request->due_date,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'message' => 'Fatura atualizada com sucesso!',
                'invoice' => $invoice
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao atualizar fatura: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $invoice = FeeInvoice::findOrFail($id);

            // Apenas faturas em draft podem ser excluídas
            if ($invoice->status !== 'draft') {
                return response()->json([
                    'message' => 'Apenas faturas em rascunho podem ser excluídas.'
                ], 422);
            }

            $invoice->delete();

            return response()->json([
                'message' => 'Fatura excluída com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao excluir fatura: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obter equipamentos com suas taxas para criação de fatura
     */
    public function getEquipmentsWithFees()
    {
        try {
            $equipments = Equipment::with([
                'fees.fee', // Relacionamento através da tabela pivot EquipmentFee
                'destination',
                'type_equipment'
            ])
            ->whereHas('fees') // Verifica se tem taxas associadas
            ->orderBy('name')
            ->get();

            // Transformar os dados para incluir as taxas diretamente
            $equipments->each(function ($equipment) {
                $equipment->active_fees = $equipment->fees->map(function ($equipmentFee) {
                    return $equipmentFee->fee;
                })->filter(); // Remove nulls se houver
            });

            return response()->json([
                'equipments' => $equipments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao carregar equipamentos: ' . $e->getMessage(),
                'equipments' => []
            ], 500);
        }
    }

    /**
     * Marcar item como pago/não pago
     */
    public function toggleItemPayment(Request $request, $invoiceId, $itemId)
    {
        $validator = Validator::make($request->all(), [
            'is_paid' => 'required|boolean',
            'payment_details' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $invoice = FeeInvoice::findOrFail($invoiceId);
            $item = FeeInvoiceItem::where('fee_invoice_id', $invoiceId)
                                  ->where('id', $itemId)
                                  ->firstOrFail();

            if ($request->is_paid) {
                $item->markAsPaid(Auth::id(), $request->payment_details ?? []);
            } else {
                $item->markAsUnpaid();
            }

            return response()->json([
                'message' => $request->is_paid ? 'Item marcado como pago!' : 'Item marcado como não pago!',
                'item' => $item->fresh()->load([
                    'equipment.destination',
                    'equipment.type_equipment',
                    'fee'
                ]),
                'invoice' => $invoice->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao atualizar pagamento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Aprovar fatura
     */
    public function approve($id)
    {
        try {
            $invoice = FeeInvoice::findOrFail($id);
            
            if ($invoice->status !== 'draft') {
                return response()->json([
                    'message' => 'Apenas faturas em rascunho podem ser aprovadas.'
                ], 422);
            }

            $invoice->markAsApproved(Auth::id());

            return response()->json([
                'message' => 'Fatura aprovada com sucesso!',
                'invoice' => $invoice->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao aprovar fatura: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Gerar relatório PDF da fatura
     */
    public function generateReport($id)
    {
        $invoice = FeeInvoice::with([
            'creator',
            'items.equipment.destination',
            'items.equipment.type_equipment',
            'items.fee'
        ])->findOrFail($id);

        // Configurações do PDF
        $pdf = PDF::loadView('admin.fee-invoices.report', compact('invoice'))
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'dpi' => 150,
                'defaultMediaType' => 'print',
                'margin_top' => 10,
                'margin_right' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10
            ])
            ->setPaper('a4', 'portrait');
        
        return $pdf->download("fatura-taxas-{$invoice->invoice_number}.pdf");
    }

    /**
     * Gerar relatório de fatura específico por destino
     */
    public function generateDestinationReport($id, $destinationId)
    {
        $invoice = FeeInvoice::with([
            'creator',
            'items.equipment.destination',
            'items.equipment.type_equipment',
            'items.fee',
            'items.equipment'
        ])->findOrFail($id);

        // Filtrar itens apenas do destino específico
        if ($destinationId === 'no-destination') {
            $filteredItems = $invoice->items->filter(function($item) {
                return is_null($item->equipment->destination_id);
            });
            $destinationName = 'Sem Destino';
        } else {
            $filteredItems = $invoice->items->filter(function($item) use ($destinationId) {
                return $item->equipment->destination_id == $destinationId;
            });
            $destinationName = $filteredItems->first()->equipment->destination->name ?? 'Destino Desconhecido';
        }

        // Criar uma cópia da fatura com os itens filtrados
        $destinationInvoice = $invoice->replicate();
        $destinationInvoice->items = $filteredItems;
        $destinationInvoice->total_amount = $filteredItems->sum('amount');
        $destinationInvoice->destination_name = $destinationName;

        $pdf = PDF::loadView('admin.fee-invoices.destination-report', compact('destinationInvoice', 'destinationName'))
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'dpi' => 150,
                'defaultMediaType' => 'print',
                'margin_top' => 10,
                'margin_right' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10
            ])
            ->setPaper('a4', 'portrait');
        
        $filename = "fatura-destino-{$destinationName}-{$invoice->invoice_number}.pdf";
        return $pdf->download($filename);
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
                'invoices' => FeeInvoice::byPeriod($currentMonth, $currentYear)->count(),
                'total_amount' => FeeInvoice::byPeriod($currentMonth, $currentYear)->sum('total_amount'),
                'paid_amount' => FeeInvoice::byPeriod($currentMonth, $currentYear)->sum('paid_amount'),
            ],
            'overdue' => [
                'count' => FeeInvoice::overdue()->count(),
                'amount' => FeeInvoice::overdue()->sum('total_amount') - FeeInvoice::overdue()->sum('paid_amount'),
            ],
            'monthly_stats' => FeeInvoice::selectRaw('month, year, COUNT(*) as count, SUM(total_amount) as total')
                                       ->where('year', $currentYear)
                                       ->groupBy('month', 'year')
                                       ->orderBy('month')
                                       ->get(),
            'top_equipments' => Equipment::withCount('feeInvoiceItems')
                                        ->having('fee_invoice_items_count', '>', 0)
                                        ->orderBy('fee_invoice_items_count', 'desc')
                                        ->limit(10)
                                        ->get(),
        ];

        return response()->json($stats);
    }
}
