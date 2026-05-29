<?php

namespace App\Http\Controllers;

use App\Models\EntryGuide;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EntryGuideVerificationController extends Controller
{
    public function verify($guideNumber)
    {
        $entryGuide = EntryGuide::with(['destination', 'creator'])
            ->where('guide_number', $guideNumber)
            ->first();

        if (!$entryGuide) {
            return response()->json([
                'valid' => false,
                'message' => 'Guia de entrada não encontrada',
                'status' => 'not_found'
            ], 404);
        }

        $now = Carbon::now();
        $isValid = $entryGuide->status === 'active' && 
                   $now->between($entryGuide->valid_from, $entryGuide->valid_until);

        $response = [
            'valid' => $isValid,
            'guide' => [
                'guide_number' => $entryGuide->guide_number,
                'guest_name' => $entryGuide->guest_name,
                'guest_document' => $entryGuide->guest_document,
                'host_name' => $entryGuide->host_name,
                'host_unit' => $entryGuide->host_unit,
                'destination' => $entryGuide->destination->name,
                'valid_from' => $entryGuide->valid_from->format('d/m/Y H:i'),
                'valid_until' => $entryGuide->valid_until->format('d/m/Y H:i'),
                'status' => $entryGuide->status,
                'purpose' => $entryGuide->purpose
            ]
        ];

        if (!$isValid) {
            if ($entryGuide->status !== 'active') {
                $response['message'] = "Guia está {$entryGuide->status_label}";
                $response['status'] = $entryGuide->status;
            } elseif ($now->isBefore($entryGuide->valid_from)) {
                $response['message'] = 'Guia ainda não é válida';
                $response['status'] = 'not_yet_valid';
            } elseif ($now->isAfter($entryGuide->valid_until)) {
                $response['message'] = 'Guia expirada';
                $response['status'] = 'expired';
            }
        } else {
            $response['message'] = 'Guia válida para acesso';
            $response['status'] = 'valid';
        }

        return response()->json($response);
    }

    public function recordEntry(Request $request, $guideNumber)
    {
        $entryGuide = EntryGuide::where('guide_number', $guideNumber)->first();

        if (!$entryGuide) {
            return response()->json([
                'success' => false,
                'message' => 'Guia não encontrada'
            ], 404);
        }

        if ($entryGuide->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Guia não está ativa'
            ], 400);
        }

        $now = Carbon::now();
        if (!$now->between($entryGuide->valid_from, $entryGuide->valid_until)) {
            return response()->json([
                'success' => false,
                'message' => 'Guia fora do período de validade'
            ], 400);
        }

        $entryGuide->update([
            'entry_time' => $now,
            'status' => 'used'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Entrada registrada com sucesso',
            'entry_time' => $now->format('d/m/Y H:i:s')
        ]);
    }

    public function recordExit(Request $request, $guideNumber)
    {
        $entryGuide = EntryGuide::where('guide_number', $guideNumber)->first();

        if (!$entryGuide) {
            return response()->json([
                'success' => false,
                'message' => 'Guia não encontrada'
            ], 404);
        }

        if ($entryGuide->status !== 'used' || !$entryGuide->entry_time) {
            return response()->json([
                'success' => false,
                'message' => 'Entrada não foi registrada para esta guia'
            ], 400);
        }

        if ($entryGuide->exit_time) {
            return response()->json([
                'success' => false,
                'message' => 'Saída já foi registrada'
            ], 400);
        }

        $entryGuide->update([
            'exit_time' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Saída registrada com sucesso',
            'exit_time' => $entryGuide->exit_time->format('d/m/Y H:i:s')
        ]);
    }
}
