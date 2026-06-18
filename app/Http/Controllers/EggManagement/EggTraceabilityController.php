<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\Egg;
use App\Models\EggModule\EggAlert;
use App\Models\EggModule\Flock;
use App\Models\EggModule\Packing;
use Illuminate\Http\Request;

class EggTraceabilityController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $query = Egg::with(['flock.house.farm', 'category', 'classification', 'inventory'])
            ->when($searchQuery, function ($q, $searchQuery) {
                $q->where(function ($sub) use ($searchQuery) {
                    $sub->where('traceability_code', 'like', "%{$searchQuery}%")
                        ->orWhereHas('flock', function ($flockQuery) use ($searchQuery) {
                            $flockQuery->where('code', 'like', "%{$searchQuery}%");
                        });
                });
            });

        if ($request->filled('flock_id')) {
            $query->where('flock_id', $request->flock_id);
        }

        $traceability = $query->orderBy('lay_date', 'desc')->paginate(15);

        return response()->json($traceability);
    }

    public function search(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $egg = Egg::where('traceability_code', 'like', '%' . $request->code . '%')
            ->with(['flock.house.farm', 'flock.lineage', 'category', 'classification', 'inventory.house'])
            ->first();

        if ($egg) {
            return response()->json([
                'type' => 'egg',
                'data' => $egg,
                'traceability_chain' => $this->getEggTraceabilityChain($egg),
            ]);
        }

        $packaging = Packing::where('qr_code', 'like', '%' . $request->code . '%')
            ->with(['classification.flock.house.farm', 'classification.flock.lineage'])
            ->first();

        if ($packaging) {
            return response()->json([
                'type' => 'packaging',
                'data' => $packaging,
                'traceability_chain' => $this->getPackagingTraceabilityChain($packaging),
            ]);
        }

        return response()->json(['message' => 'Nenhum registo de rastreabilidade encontrado'], 404);
    }

    public function byFlock(Flock $flock)
    {
        $eggs = Egg::where('flock_id', $flock->id)
            ->with('category')
            ->orderBy('lay_date', 'desc')
            ->get();

        $summary = [
            'flock' => $flock->load('house.farm', 'lineage'),
            'total_eggs' => $eggs->count(),
            'by_category' => $eggs->groupBy(fn ($egg) => $egg->category->name ?? 'Sem categoria')->map->count(),
            'by_quality' => $eggs->groupBy('quality')->map->count(),
            'eggs' => $eggs,
        ];

        return response()->json($summary);
    }

    public function byPackage(Packing $packaging)
    {
        $packaging->load('classification.flock.house.farm', 'classification.flock.lineage');

        return response()->json([
            'packaging' => $packaging,
            'traceability_chain' => $this->getPackagingTraceabilityChain($packaging),
        ]);
    }

    public function byDateRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $eggs = Egg::whereBetween('lay_date', [$request->start_date, $request->end_date])
            ->with(['flock.house.farm', 'category'])
            ->orderBy('lay_date', 'desc')
            ->get();

        return response()->json($eggs);
    }

    public function showByQrCode($code)
    {
        $packaging = Packing::where('qr_code', $code)
            ->with(['classification.flock.house.farm'])
            ->first();

        if (!$packaging) {
            return response()->json(['message' => 'QR Code não encontrado'], 404);
        }

        $farmName = $packaging->classification?->flock?->house?->farm?->name ?? 'Desconhecida';

        return response()->json([
            'product' => 'Ovos Frescos',
            'packaging_date' => $packaging->created_at->format('Y-m-d'),
            'expiry_date' => $packaging->expiry_date->format('Y-m-d'),
            'origin' => $farmName,
            'batch_number' => $packaging->qr_code,
            'certification' => 'Produto certificado para qualidade',
        ]);
    }

    public function export(Request $request)
    {
        $query = Egg::with(['flock.house.farm', 'category']);

        if ($request->filled('start_date')) {
            $query->where('lay_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('lay_date', '<=', $request->end_date);
        }

        $export = $query->orderBy('lay_date', 'desc')->get()->map(function ($egg) {
            return [
                'traceability_code' => $egg->traceability_code,
                'lay_date' => $egg->lay_date,
                'quality' => $egg->quality,
                'category' => $egg->category->name ?? null,
                'farm' => $egg->flock->house->farm->name ?? null,
                'house' => $egg->flock->house->name ?? null,
                'flock_code' => $egg->flock->code ?? null,
                'destination' => $egg->destination,
            ];
        });

        return response()->json($export);
    }

    private function getEggTraceabilityChain(Egg $egg)
    {
        $egg->loadMissing(['flock.house.farm', 'flock.lineage', 'category', 'classification']);

        return [
            'egg' => [
                'code' => $egg->traceability_code,
                'lay_date' => $egg->lay_date,
                'quality' => $egg->quality,
                'category' => $egg->category->name ?? null,
                'destination' => $egg->destination,
            ],
            'flock' => $egg->flock ? [
                'code' => $egg->flock->code,
                'lineage' => $egg->flock->lineage->name ?? null,
                'housing_date' => $egg->flock->housing_date,
            ] : null,
            'house' => $egg->flock?->house ? [
                'name' => $egg->flock->house->name,
                'farm' => $egg->flock->house->farm->name ?? null,
            ] : null,
            'classification' => $egg->classification ? [
                'date' => $egg->classification->processing_date,
                'reject_percentage' => $egg->classification->reject_percentage,
            ] : null,
            'inventory' => $egg->inventory ? [
                'quantity' => $egg->inventory->quantity,
                'status' => $egg->inventory->status,
                'location' => $egg->inventory->location,
            ] : null,
        ];
    }

    private function getPackagingTraceabilityChain(Packing $packaging)
    {
        $packaging->loadMissing(['classification.flock.house.farm', 'classification.flock.lineage']);
        $flock = $packaging->classification->flock ?? null;

        return [
            'packaging' => [
                'qr_code' => $packaging->qr_code,
                'type' => $packaging->package_type,
                'packaged_eggs' => $packaging->packaged_eggs,
                'expiry_date' => $packaging->expiry_date,
            ],
            'classification' => [
                'date' => $packaging->classification->processing_date,
                'washed_eggs' => $packaging->classification->washed_eggs,
                'reject_percentage' => $packaging->classification->reject_percentage,
            ],
            'flock' => $flock ? [
                'code' => $flock->code,
                'lineage' => $flock->lineage->name ?? null,
            ] : null,
            'house' => $flock?->house ? [
                'name' => $flock->house->name,
                'farm' => $flock->house->farm->name ?? null,
            ] : null,
        ];
    }
}
