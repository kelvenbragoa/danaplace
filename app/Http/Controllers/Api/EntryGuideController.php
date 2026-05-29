<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EntryGuide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EntryGuideController extends Controller
{
    /**
     * Buscar guia por número ou QR code
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGuide(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'guide_number' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Número da guia é obrigatório',
                    'errors' => $validator->errors()
                ], 422);
            }

            $guide = EntryGuide::with(['destination', 'creator'])
                ->where('guide_number', $request->guide_number)
                ->first();

            if (!$guide) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guia não encontrada'
                ], 404);
            }

            // Verificar se a guia está válida
            $now = Carbon::now();
            $isValid = $guide->status === 'active' && 
                      $now->between($guide->valid_from, $guide->valid_until);

            $status_info = $this->getStatusInfo($guide, $now);

            return response()->json([
                'success' => true,
                'data' => [
                    'guide' => [
                        'id' => $guide->id,
                        'guide_number' => $guide->guide_number,
                        'guest_name' => $guide->guest_name,
                        'guest_document' => $guide->guest_document,
                        'guest_phone' => $guide->guest_phone,
                        'guest_email' => $guide->guest_email,
                        'host_name' => $guide->host_name,
                        'host_unit' => $guide->host_unit,
                        'purpose' => $guide->purpose,
                        'destination' => $guide->destination ? [
                            'id' => $guide->destination->id,
                            'name' => $guide->destination->name
                        ] : null,
                        'valid_from' => $guide->valid_from->format('d/m/Y H:i'),
                        'valid_until' => $guide->valid_until->format('d/m/Y H:i'),
                        'entry_time' => $guide->entry_time ? $guide->entry_time->format('d/m/Y H:i') : null,
                        'exit_time' => $guide->exit_time ? $guide->exit_time->format('d/m/Y H:i') : null,
                        'status' => $guide->status,
                        'status_label' => $guide->status_label,
                        'is_valid' => $isValid,
                        'is_expired' => $guide->is_expired,
                        'observations' => $guide->observations,
                        'created_at' => $guide->created_at->format('d/m/Y H:i')
                    ],
                    'status_info' => $status_info,
                    'can_enter' => $status_info['can_enter'],
                    'can_exit' => $status_info['can_exit']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar guia',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Registrar entrada do visitante
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordEntry(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'guide_number' => 'required|string',
                'location_lat' => 'nullable|numeric',
                'location_lng' => 'nullable|numeric',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $guide = EntryGuide::where('guide_number', $request->guide_number)->first();

            if (!$guide) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guia não encontrada'
                ], 404);
            }

            $now = Carbon::now();
            $status_info = $this->getStatusInfo($guide, $now);

            if (!$status_info['can_enter']) {
                return response()->json([
                    'success' => false,
                    'message' => $status_info['message']
                ], 422);
            }

            // Registrar entrada
            $guide->update([
                'entry_time' => $now,
                'observations' => $request->notes ? 
                    ($guide->observations ? $guide->observations . "\n\nEntrada: " . $request->notes : "Entrada: " . $request->notes) : 
                    $guide->observations
            ]);

            // Log da entrada (opcional - você pode criar uma tabela de logs se necessário)
            \Log::info('Entry recorded', [
                'guide_id' => $guide->id,
                'guide_number' => $guide->guide_number,
                'guest_name' => $guide->guest_name,
                'entry_time' => $now,
                'location' => $request->location_lat && $request->location_lng ? 
                    ['lat' => $request->location_lat, 'lng' => $request->location_lng] : null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Entrada registrada com sucesso',
                'data' => [
                    'guide_number' => $guide->guide_number,
                    'guest_name' => $guide->guest_name,
                    'entry_time' => $guide->entry_time->format('d/m/Y H:i:s'),
                    'destination' => $guide->destination->name ?? 'N/A'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar entrada',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Registrar saída do visitante
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordExit(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'guide_number' => 'required|string',
                'location_lat' => 'nullable|numeric',
                'location_lng' => 'nullable|numeric',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $guide = EntryGuide::where('guide_number', $request->guide_number)->first();

            if (!$guide) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guia não encontrada'
                ], 404);
            }

            $now = Carbon::now();
            $status_info = $this->getStatusInfo($guide, $now);

            if (!$status_info['can_exit']) {
                return response()->json([
                    'success' => false,
                    'message' => $status_info['message']
                ], 422);
            }

            // Calcular duração da visita
            $duration_minutes = $guide->entry_time ? 
                $guide->entry_time->diffInMinutes($now) : 0;

            // Registrar saída e marcar como utilizada
            $guide->update([
                'exit_time' => $now,
                'status' => 'used', // Marcar como utilizada após a saída
                'observations' => $request->notes ? 
                    ($guide->observations ? $guide->observations . "\n\nSaída: " . $request->notes : "Saída: " . $request->notes) : 
                    $guide->observations
            ]);

            // Log da saída
            \Log::info('Exit recorded', [
                'guide_id' => $guide->id,
                'guide_number' => $guide->guide_number,
                'guest_name' => $guide->guest_name,
                'entry_time' => $guide->entry_time,
                'exit_time' => $now,
                'duration_minutes' => $duration_minutes,
                'location' => $request->location_lat && $request->location_lng ? 
                    ['lat' => $request->location_lat, 'lng' => $request->location_lng] : null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Saída registrada com sucesso',
                'data' => [
                    'guide_number' => $guide->guide_number,
                    'guest_name' => $guide->guest_name,
                    'entry_time' => $guide->entry_time ? $guide->entry_time->format('d/m/Y H:i:s') : null,
                    'exit_time' => $guide->exit_time->format('d/m/Y H:i:s'),
                    'duration' => $this->formatDuration($duration_minutes),
                    'destination' => $guide->destination->name ?? 'N/A'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar saída',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Listar guias válidas (para testes/debug)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listValidGuides(Request $request)
    {
        try {
            $guides = EntryGuide::with(['destination'])
                ->where('status', 'active')
                ->where('valid_from', '<=', Carbon::now())
                ->where('valid_until', '>=', Carbon::now())
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            $guides_formatted = $guides->map(function ($guide) {
                return [
                    'guide_number' => $guide->guide_number,
                    'guest_name' => $guide->guest_name,
                    'destination' => $guide->destination->name ?? 'N/A',
                    'valid_until' => $guide->valid_until->format('d/m/Y H:i'),
                    'entry_time' => $guide->entry_time ? $guide->entry_time->format('d/m/Y H:i') : null,
                    'exit_time' => $guide->exit_time ? $guide->exit_time->format('d/m/Y H:i') : null,
                    'status' => $guide->status_label
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $guides_formatted
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar guias',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Obter informações de status da guia
     * 
     * @param EntryGuide $guide
     * @param Carbon $now
     * @return array
     */
    private function getStatusInfo($guide, $now)
    {
        // Guia cancelada
        if ($guide->status === 'cancelled') {
            return [
                'status' => 'cancelled',
                'message' => 'Guia foi cancelada',
                'can_enter' => false,
                'can_exit' => false
            ];
        }

        // Guia já utilizada (entrada e saída já registradas)
        if ($guide->status === 'used') {
            return [
                'status' => 'used',
                'message' => 'Guia já foi utilizada',
                'can_enter' => false,
                'can_exit' => false
            ];
        }

        // Guia expirada
        if ($now->isAfter($guide->valid_until)) {
            return [
                'status' => 'expired',
                'message' => 'Guia expirou em ' . $guide->valid_until->format('d/m/Y H:i'),
                'can_enter' => false,
                'can_exit' => false
            ];
        }

        // Guia ainda não válida
        if ($now->isBefore($guide->valid_from)) {
            return [
                'status' => 'not_valid_yet',
                'message' => 'Guia será válida a partir de ' . $guide->valid_from->format('d/m/Y H:i'),
                'can_enter' => false,
                'can_exit' => false
            ];
        }

        // Guia ativa e válida
        if ($guide->status === 'active') {
            // Já fez entrada, pode fazer saída
            if ($guide->entry_time && !$guide->exit_time) {
                return [
                    'status' => 'entered',
                    'message' => 'Visitante já fez entrada em ' . $guide->entry_time->format('d/m/Y H:i'),
                    'can_enter' => false,
                    'can_exit' => true
                ];
            }

            // Ainda não fez entrada
            if (!$guide->entry_time) {
                return [
                    'status' => 'valid',
                    'message' => 'Guia válida para entrada',
                    'can_enter' => true,
                    'can_exit' => false
                ];
            }
        }

        // Caso não coberto
        return [
            'status' => 'unknown',
            'message' => 'Status desconhecido',
            'can_enter' => false,
            'can_exit' => false
        ];
    }

    /**
     * Formatar duração em formato legível
     * 
     * @param int $minutes
     * @return string
     */
    private function formatDuration($minutes)
    {
        if ($minutes < 60) {
            return $minutes . ' minutos';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours == 1) {
            return $remainingMinutes > 0 ? 
                "1 hora e {$remainingMinutes} minutos" : 
                "1 hora";
        }

        return $remainingMinutes > 0 ? 
            "{$hours} horas e {$remainingMinutes} minutos" : 
            "{$hours} horas";
    }
}