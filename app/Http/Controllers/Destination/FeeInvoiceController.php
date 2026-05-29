<?php

namespace App\Http\Controllers\Destination;

use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class FeeInvoiceController extends Controller
{
    /**
     * Display a listing of the invoices for the authenticated destination.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Buscar o destino associado ao usuário logado
        $destination = Destination::find($user->destination_id);
        
        if (!$destination) {
            return response()->json(['message' => 'Destino não encontrado para este usuário.'], 404);
        }

        $query = FeeInvoice::with([
                'items.equipment.destination',
                'items.fee',
                'creator'
            ])
            ->whereHas('items.equipment', function($q) use ($destination) {
                $q->where('destination_id', $destination->id);
            })
            ->orderBy('created_at', 'desc');

        // Filtros opcionais
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('period_month') && $request->filled('period_year')) {
            $query->where('period_month', $request->period_month)
                  ->where('period_year', $request->period_year);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('period_description', 'like', "%{$search}%");
            });
        }

        $invoices = $query->paginate(10);

        return response()->json($invoices);
    }

    /**
     * Display the specified invoice if it belongs to the authenticated destination.
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // Buscar o destino associado ao usuário logado
        $destination = Destination::find($user->destination_id);
        
        if (!$destination) {
            return response()->json(['message' => 'Destino não encontrado para este usuário.'], 404);
        }

        $invoice = FeeInvoice::with([
                'items.equipment.destination',
                'items.fee',
                'creator'
            ])
            ->whereHas('items.equipment', function($q) use ($destination) {
                $q->where('destination_id', $destination->id);
            })
            ->findOrFail($id);

        // Filtrar apenas os itens que pertencem a este destino
        $invoice->items = $invoice->items->filter(function($item) use ($destination) {
            return $item->equipment->destination_id == $destination->id;
        });

        // Recalcular totais baseado apenas nos itens deste destino
        $invoice->total_amount = $invoice->items->sum('amount');
        $invoice->paid_amount = $invoice->items->where('is_paid', true)->sum('amount');
        $invoice->remaining_amount = $invoice->total_amount - $invoice->paid_amount;

        return response()->json($invoice);
    }

    /**
     * Generate PDF report for the specific destination's invoice.
     */
    public function generateReport($id)
    {
        $user = Auth::user();
        
        // Buscar o destino associado ao usuário logado
        $destination = Destination::find($user->destination_id);
        
        if (!$destination) {
            return response()->json(['message' => 'Destino não encontrado para este usuário.'], 404);
        }

        $invoice = FeeInvoice::with([
                'items.equipment.destination',
                'items.fee',
                'creator'
            ])
            ->whereHas('items.equipment', function($q) use ($destination) {
                $q->where('destination_id', $destination->id);
            })
            ->findOrFail($id);

        // Filtrar apenas os itens que pertencem a este destino
        $invoice->items = $invoice->items->filter(function($item) use ($destination) {
            return $item->equipment->destination_id == $destination->id;
        });

        // Recalcular totais baseado apenas nos itens deste destino
        $invoice->total_amount = $invoice->items->sum('amount');
        $invoice->paid_amount = $invoice->items->where('is_paid', true)->sum('amount');
        $invoice->remaining_amount = $invoice->total_amount - $invoice->paid_amount;

        // Gerar PDF
        $pdf = Pdf::loadView('destination.fee-invoices.report', compact('invoice', 'destination'));
        
        // Configurações do PDF
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif',
            'margin-top' => '0.5in',
            'margin-right' => '0.5in',
            'margin-bottom' => '0.5in',
            'margin-left' => '0.5in',
        ]);

        $filename = 'fatura-taxas-' . $invoice->invoice_number . '-' . $destination->name . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Get summary statistics for the destination's invoices.
     */
    public function statistics()
    {
        $user = Auth::user();
        
        // Buscar o destino associado ao usuário logado
        $destination = Destination::find($user->destination_id);
        
        if (!$destination) {
            return response()->json(['message' => 'Destino não encontrado para este usuário.'], 404);
        }

        $totalInvoices = FeeInvoice::whereHas('items.equipment', function($q) use ($destination) {
            $q->where('destination_id', $destination->id);
        })->count();

        $paidInvoices = FeeInvoice::whereHas('items.equipment', function($q) use ($destination) {
            $q->where('destination_id', $destination->id);
        })->where('status', 'paid')->count();

        $pendingInvoices = FeeInvoice::whereHas('items.equipment', function($q) use ($destination) {
            $q->where('destination_id', $destination->id);
        })->whereIn('status', ['issued', 'partially_paid'])->count();

        $overdueInvoices = FeeInvoice::whereHas('items.equipment', function($q) use ($destination) {
            $q->where('destination_id', $destination->id);
        })->where('status', 'overdue')->count();

        // Cálculo de valores totais
        $totalAmount = FeeInvoice::whereHas('items.equipment', function($q) use ($destination) {
            $q->where('destination_id', $destination->id);
        })->sum('total_amount');

        $paidAmount = FeeInvoice::whereHas('items.equipment', function($q) use ($destination) {
            $q->where('destination_id', $destination->id);
        })->sum('paid_amount');

        $pendingAmount = $totalAmount - $paidAmount;

        return response()->json([
            'total_invoices' => $totalInvoices,
            'paid_invoices' => $paidInvoices,
            'pending_invoices' => $pendingInvoices,
            'overdue_invoices' => $overdueInvoices,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'pending_amount' => $pendingAmount,
            'destination' => $destination
        ]);
    }
}