<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\Packing;
use Illuminate\Http\Request;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PackagingController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $query = Packing::with('classification.flock')
            ->when($searchQuery, function ($q, $searchQuery) {
                $q->where(function ($sub) use ($searchQuery) {
                    $sub->where('qr_code', 'like', "%{$searchQuery}%")
                        ->orWhereHas('classification.flock', function ($flockQuery) use ($searchQuery) {
                            $flockQuery->where('code', 'like', "%{$searchQuery}%");
                        });
                });
            });

        if ($request->filled('classification_id')) {
            $query->where('classification_id', $request->classification_id);
        }

        $packaging = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($packaging);
    }

    public function show(Packing $packaging)
    {
        return response()->json($packaging->load('classification.flock.house'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'classification_id' => 'required|exists:egg_classifications,id',
            'package_type' => 'required|in:tray,box',
            'quantity_used' => 'required|integer|min:1',
            'packaged_eggs' => 'required|integer|min:1',
            'remaining_eggs' => 'integer|min:0',
            'expiry_date' => 'required|date'
        ]);

        $validated['qr_code'] = $this->generateQrCodeString();
        $packaging = Packing::create($validated);
        
        return response()->json($packaging, 201);
    }

    public function generateQrCode(Packing $packaging)
    {
        $this->generateQRCodeNew($packaging);
        return response()->json(['qr_code' => $packaging->qr_code]);
    }

    public function validateQrCode(Request $request)
    {
        $request->validate(['qr_code' => 'required|string']);
        
        $packaging = Packing::where('qr_code', $request->qr_code)->first();
        
        if (!$packaging) {
            return response()->json(['valid' => false, 'message' => 'Invalid QR Code'], 404);
        }
        
        return response()->json([
            'valid' => true,
            'data' => $packaging->load('classification.flock')
        ]);
    }

    public function update(Request $request, Packing $packaging)
    {
        $validated = $request->validate([
            'package_type' => 'in:tray,box',
            'quantity_used' => 'integer|min:1',
            'packaged_eggs' => 'integer|min:1',
            'remaining_eggs' => 'integer|min:0',
            'expiry_date' => 'date'
        ]);

        $packaging->update($validated);
        return response()->json($packaging);
    }

    public function destroy(Packing $packaging)
    {
        $packaging->delete();
        return response()->json(['message' => 'Packaging record deleted successfully']);
    }

    private function generateQrCodeString()
    {
        return 'PKG-' . strtoupper(uniqid()) . '-' . date('YmdHis');
    }

    private function generateQRCodeNew(Packing $packaging)
    {
        // Create QR code data  
        $qrData = json_encode([
            'packaging_id' => $packaging->id,
            'guide_number' => $packaging->guide_number,
            'guest_name' => $packaging->guest_name,
            'destination' => $packaging->classification->flock->destination->name,
            'valid_until' => $packaging->expiry_date->toISOString()
        ]);

        try {
            // Create QR code
            $qrCode = new QrCode($qrData);
            
            // Create writer and generate PNG
            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            // Generate filename for S3
            $filename = "packaging-qr-{$packaging->id}.png";
            
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
                $packaging->update([
                    'qr_code_path' => $fullPath,
                    'qr_code' => $qrCodeUrl
                ]);
            }
            
        } catch (\Exception $e) {
            // Log error but don't fail the creation
            Log::error('Failed to generate QR code for packaging ' . $packaging->id . ': ' . $e->getMessage());
        }
    }
}
