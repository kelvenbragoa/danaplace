<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EntryGuide;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class EntryGuideController extends Controller
{
    public function index(Request $request)
    {
        $query = EntryGuide::with(['destination', 'creator'])
            ->orderBy('created_at', 'desc');

        // Filter by destination if user has specific destination access
        if (Auth::user()->destination_id) {
            $query->where('destination_id', Auth::user()->destination_id);
        }

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                  ->orWhere('guest_document', 'like', "%{$search}%")
                  ->orWhere('guide_number', 'like', "%{$search}%")
                  ->orWhere('host_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('destination_id')) {
            $query->where('destination_id', $request->destination_id);
        }

        $entryGuides = $query->paginate(15);
        $destinations = Destination::orderBy('name')->get();

        return response()->json([
            'entryGuides' => $entryGuides,
            'destinations' => $destinations,
            'filters' => $request->only(['search', 'status', 'destination_id'])
        ]);
    }

    public function create()
    {
        $destinations = Destination::orderBy('name')->get();
        
        // If user has specific destination, filter it
        if (Auth::user()->destination_id) {
            $destinations = $destinations->where('id', Auth::user()->destination_id);
        }

        return response()->json([
            'destinations' => $destinations
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination_id' => 'required|exists:destinations,id',
            'visitor_name' => 'required|string|max:255',
            'visitor_document' => 'required|string|max:50',
            'visitor_phone' => 'nullable|string|max:20',
            'visitor_company' => 'nullable|string|max:255',
            'host_name' => 'required|string|max:255',
            'host_phone' => 'nullable|string|max:20',
            'specific_location' => 'nullable|string|max:100',
            'purpose' => 'nullable|string|max:500',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from'
        ]);

        // Map frontend fields to backend fields
        $validated['guest_name'] = $validated['visitor_name'];
        $validated['guest_document'] = $validated['visitor_document'];
        $validated['guest_phone'] = $validated['visitor_phone'] ?? null;
        $validated['guest_email'] = $validated['visitor_company'] ?? null; // Using email field for company
        $validated['host_unit'] = $validated['specific_location'] ?? 'N/A';
        
        // Remove frontend field names
        unset($validated['visitor_name'], $validated['visitor_document'], $validated['visitor_phone'], $validated['visitor_company'], $validated['specific_location']);

        // Check if user has permission for this destination
        if (Auth::user()->destination_id && Auth::user()->destination_id != $validated['destination_id']) {
            abort(403, 'Não autorizado para este destino');
        }

        $validated['guide_number'] = EntryGuide::generateGuideNumber();
        $validated['created_by'] = Auth::id();

        $entryGuide = EntryGuide::create($validated);

        // Generate QR Code
        $this->generateQRCode($entryGuide);

        return response()->json([
            'entryGuide' => $entryGuide,
            'message' => 'Guia de entrada criada com sucesso!'
        ], 201);
    }

    public function show(EntryGuide $entryGuide)
    {
        // Check if user has permission to view this guide
        if (Auth::user()->destination_id && Auth::user()->destination_id != $entryGuide->destination_id) {
            abort(403, 'Não autorizado para visualizar esta guia');
        }

        $entryGuide->load(['destination', 'creator']);

        return response()->json([
            'entryGuide' => $entryGuide
        ]);
    }

    public function edit(EntryGuide $entryGuide)
    {
        // Check if user has permission and guide is editable
        if (Auth::user()->destination_id && Auth::user()->destination_id != $entryGuide->destination_id) {
            abort(403, 'Não autorizado para editar esta guia');
        }

        if ($entryGuide->status !== 'active') {
            return response()->json([
                'message' => 'Apenas guias ativas podem ser editadas'
            ], 422);
        }

        $destinations = Destination::orderBy('name')->get();
        
        if (Auth::user()->destination_id) {
            $destinations = $destinations->where('id', Auth::user()->destination_id);
        }

        return response()->json([
            'entryGuide' => $entryGuide,
            'destinations' => $destinations
        ]);
    }

    public function update(Request $request, EntryGuide $entryGuide)
    {
        // Check permissions
        if (Auth::user()->destination_id && Auth::user()->destination_id != $entryGuide->destination_id) {
            abort(403, 'Não autorizado para atualizar esta guia');
        }

        if ($entryGuide->status !== 'active') {
            return response()->json([
                'message' => 'Apenas guias ativas podem ser editadas'
            ], 422);
        }

        $validated = $request->validate([
            'visitor_name' => 'required|string|max:255',
            'visitor_document' => 'required|string|max:50',
            'visitor_phone' => 'nullable|string|max:20',
            'visitor_company' => 'nullable|string|max:255',
            'host_name' => 'required|string|max:255',
            'host_phone' => 'nullable|string|max:20',
            'destination_id' => 'required|exists:destinations,id',
            'specific_location' => 'nullable|string|max:100',
            'purpose' => 'nullable|string|max:500',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from'
        ]);

        // Map frontend fields to backend fields
        $validated['guest_name'] = $validated['visitor_name'];
        $validated['guest_document'] = $validated['visitor_document'];
        $validated['guest_phone'] = $validated['visitor_phone'] ?? null;
        $validated['guest_email'] = $validated['visitor_company'] ?? null;
        $validated['host_unit'] = $validated['specific_location'] ?? 'N/A';
        
        // Remove frontend field names
        unset($validated['visitor_name'], $validated['visitor_document'], $validated['visitor_phone'], $validated['visitor_company'], $validated['specific_location']);

        $entryGuide->update($validated);

        // Regenerate QR Code if data changed
        $this->generateQRCode($entryGuide->fresh(['destination']));

        return response()->json([
            'entryGuide' => $entryGuide->fresh(['destination']),
            'message' => 'Guia de entrada atualizada com sucesso!'
        ]);
    }

    public function destroy(EntryGuide $entryGuide)
    {
        // Check permissions
        if (Auth::user()->destination_id && Auth::user()->destination_id != $entryGuide->destination_id) {
            abort(403, 'Não autorizado para excluir esta guia');
        }

        // Only allow deletion of active guides
        if ($entryGuide->status !== 'active') {
            return response()->json([
                'message' => 'Apenas guias ativas podem ser excluídas'
            ], 422);
        }

        // Delete QR code file from S3 if exists
        if ($entryGuide->qr_code_path && Storage::disk('s3')->exists($entryGuide->qr_code_path)) {
            Storage::disk('s3')->delete($entryGuide->qr_code_path);
        }

        $entryGuide->delete();

        return response()->json([
            'message' => 'Guia de entrada excluída com sucesso!'
        ]);
    }

    public function downloadPdf(EntryGuide $entryGuide)
    {
        // Check permissions
        if (Auth::user()->destination_id && Auth::user()->destination_id != $entryGuide->destination_id) {
            abort(403, 'Não autorizado para baixar esta guia');
        }

        $entryGuide->load('destination');

        $pdf = Pdf::loadView('admin.entry-guides.pdf', compact('entryGuide'));
        
        $filename = "guia-entrada-{$entryGuide->guide_number}.pdf";
        
        return $pdf->download($filename);
    }

    public function cancel(EntryGuide $entryGuide)
    {
        // Check permissions
        if (Auth::user()->destination_id && Auth::user()->destination_id != $entryGuide->destination_id) {
            abort(403, 'Não autorizado para cancelar esta guia');
        }

        if ($entryGuide->status !== 'active') {
            return response()->json([
                'message' => 'Apenas guias ativas podem ser canceladas'
            ], 422);
        }

        $entryGuide->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Guia de entrada cancelada com sucesso!'
        ]);
    }

    private function generateQRCode(EntryGuide $entryGuide)
    {
        // Create QR code data
        $qrData = json_encode([
            'guide_id' => $entryGuide->id,
            'guide_number' => $entryGuide->guide_number,
            'guest_name' => $entryGuide->guest_name,
            'destination' => $entryGuide->destination->name,
            'valid_until' => $entryGuide->valid_until->toISOString()
        ]);

        try {
            // Create QR code
            $qrCode = new QrCode($qrData);
            
            // Create writer and generate PNG
            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            // Generate filename for S3
            $filename = "entry-guide-qr-{$entryGuide->id}.png";
            
            // Save QR code to S3
            $fullPath = "qr-codes/{$filename}";
            $uploaded = Storage::disk('s3')->put($fullPath, $result->getString());
            
            if ($uploaded) {
                // Create the full S3 URL 
                $s3Bucket = config('filesystems.disks.s3.bucket');
                $s3Region = config('filesystems.disks.s3.region');
                $awsUrl = config('filesystems.disks.s3.url');
                
                // Use AWS_URL if configured, otherwise build URL
                if ($awsUrl) {
                    $qrCodeUrl = rtrim($awsUrl, '/') . '/' . $fullPath;
                } else {
                    $qrCodeUrl = "https://{$s3Bucket}.s3.{$s3Region}.amazonaws.com/{$fullPath}";
                }

                // Update entry guide with QR code path and URL
                $entryGuide->update([
                    'qr_code_path' => $fullPath,
                    'qr_code' => $qrCodeUrl
                ]);
            }
            
        } catch (\Exception $e) {
            // Log error but don't fail the creation
            Log::error('Failed to generate QR code for entry guide ' . $entryGuide->id . ': ' . $e->getMessage());
        }
    }
}
