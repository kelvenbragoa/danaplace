<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\DailyProduction;
use App\Models\EggModule\Egg;
use App\Models\EggModule\EggClassification;
use App\Models\EggModule\EggInventory;
use App\Models\EggModule\EggOrder;
use App\Models\EggModule\Mortality;
use App\Models\EggModule\VaccineSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EggReportController extends Controller
{
    public function dailyProduction(Request $request)
    {
        $query = DailyProduction::with('flock.house.farm')
            ->when($request->filled('start_date'), fn ($q) => $q->where('date', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->where('date', '<=', $request->end_date))
            ->when($request->filled('flock_id'), fn ($q) => $q->where('flock_id', $request->flock_id));

        $summary = (clone $query)->selectRaw('
            COUNT(*) as records,
            SUM(total_eggs) as total_eggs,
            SUM(cracked_eggs) as cracked_eggs,
            SUM(dirty_eggs) as dirty_eggs,
            SUM(deformed_eggs) as deformed_eggs,
            SUM(clean_eggs) as clean_eggs,
            SUM(feed_consumption_kg) as feed_consumption_kg,
            SUM(water_consumption_liters) as water_consumption_liters
        ')->first();

        $byDate = (clone $query)->selectRaw('
            DATE(date) as date,
            SUM(total_eggs) as total_eggs,
            SUM(cracked_eggs) as cracked_eggs,
            SUM(dirty_eggs) as dirty_eggs,
            SUM(deformed_eggs) as deformed_eggs
        ')->groupBy('date')->orderBy('date', 'desc')->get();

        $details = $query->orderBy('date', 'desc')->paginate(20);

        return response()->json([
            'summary' => $summary,
            'by_date' => $byDate,
            'details' => $details,
        ]);
    }

    public function rejects(Request $request)
    {
        $productionQuery = DailyProduction::query()
            ->when($request->filled('start_date'), fn ($q) => $q->where('date', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->where('date', '<=', $request->end_date))
            ->when($request->filled('flock_id'), fn ($q) => $q->where('flock_id', $request->flock_id));

        $productionSummary = (clone $productionQuery)->selectRaw('
            SUM(total_eggs) as total_eggs,
            SUM(cracked_eggs) as cracked_eggs,
            SUM(dirty_eggs) as dirty_eggs,
            SUM(deformed_eggs) as deformed_eggs
        ')->first();

        $totalRejects = ($productionSummary->cracked_eggs ?? 0)
            + ($productionSummary->dirty_eggs ?? 0)
            + ($productionSummary->deformed_eggs ?? 0);

        $rejectRate = ($productionSummary->total_eggs ?? 0) > 0
            ? round(($totalRejects / $productionSummary->total_eggs) * 100, 2)
            : 0;

        $classificationQuery = EggClassification::with('flock')
            ->when($request->filled('start_date'), fn ($q) => $q->where('processing_date', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->where('processing_date', '<=', $request->end_date))
            ->when($request->filled('flock_id'), fn ($q) => $q->where('flock_id', $request->flock_id));

        $byClassification = (clone $classificationQuery)->selectRaw('
            DATE(processing_date) as date,
            SUM(total_rejects) as total_rejects,
            SUM(washed_eggs + unwashed_eggs) as total_processed,
            AVG(reject_percentage) as avg_reject_percentage
        ')->groupBy('date')->orderBy('date', 'desc')->get();

        $rejectEggs = Egg::with('flock', 'category')
            ->where('destination', 'reject')
            ->when($request->filled('start_date'), fn ($q) => $q->where('lay_date', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->where('lay_date', '<=', $request->end_date))
            ->when($request->filled('flock_id'), fn ($q) => $q->where('flock_id', $request->flock_id))
            ->orderBy('lay_date', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'summary' => [
                'total_eggs' => $productionSummary->total_eggs ?? 0,
                'total_rejects' => $totalRejects,
                'reject_rate' => $rejectRate,
                'cracked' => $productionSummary->cracked_eggs ?? 0,
                'dirty' => $productionSummary->dirty_eggs ?? 0,
                'deformed' => $productionSummary->deformed_eggs ?? 0,
            ],
            'by_classification_date' => $byClassification,
            'reject_eggs' => $rejectEggs,
        ]);
    }

    public function inventory(Request $request)
    {
        $query = EggInventory::with('egg.category', 'egg.flock', 'house.farm')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('house_id'), fn ($q) => $q->where('house_id', $request->house_id));

        $summary = [
            'total_records' => (clone $query)->count(),
            'total_quantity' => (clone $query)->sum('quantity'),
            'available' => (clone $query)->where('status', 'available')->sum('quantity'),
            'reserved' => (clone $query)->where('status', 'reserved')->sum('quantity'),
            'shipped' => (clone $query)->where('status', 'shipped')->sum('quantity'),
        ];

        $byCategory = (clone $query)->get()
            ->groupBy(fn ($item) => $item->egg->category->name ?? 'Sem categoria')
            ->map(fn ($items, $category) => [
                'category' => $category,
                'quantity' => $items->sum('quantity'),
                'records' => $items->count(),
            ])
            ->values();

        $details = $query->orderBy('entry_date', 'desc')->paginate(20);

        return response()->json([
            'summary' => $summary,
            'by_category' => $byCategory,
            'details' => $details,
        ]);
    }

    public function sanitary(Request $request)
    {
        $vaccinationQuery = VaccineSchedule::with('flock', 'vaccine', 'responsible')
            ->when($request->filled('start_date'), fn ($q) => $q->where('scheduled_date', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->where('scheduled_date', '<=', $request->end_date))
            ->when($request->filled('flock_id'), fn ($q) => $q->where('flock_id', $request->flock_id));

        $mortalityQuery = Mortality::with('flock', 'responsible')
            ->when($request->filled('start_date'), fn ($q) => $q->where('date', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->where('date', '<=', $request->end_date))
            ->when($request->filled('flock_id'), fn ($q) => $q->where('flock_id', $request->flock_id));

        $summary = [
            'vaccinations_total' => (clone $vaccinationQuery)->count(),
            'vaccinations_applied' => (clone $vaccinationQuery)->where('status', 'applied')->count(),
            'vaccinations_pending' => (clone $vaccinationQuery)->where('status', 'pending')->count(),
            'mortality_total' => (clone $mortalityQuery)->sum('quantity'),
            'mortality_records' => (clone $mortalityQuery)->count(),
        ];

        return response()->json([
            'summary' => $summary,
            'vaccinations' => $vaccinationQuery->orderBy('scheduled_date', 'desc')->paginate(15),
            'mortality' => $mortalityQuery->orderBy('date', 'desc')->paginate(15),
        ]);
    }

    public function traceability(Request $request)
    {
        $query = Egg::with(['flock.house.farm', 'category'])
            ->when($request->filled('start_date'), fn ($q) => $q->where('lay_date', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->where('lay_date', '<=', $request->end_date))
            ->when($request->filled('flock_id'), fn ($q) => $q->where('flock_id', $request->flock_id));

        $summary = [
            'total_eggs' => (clone $query)->count(),
            'by_quality' => (clone $query)->get()->groupBy('quality')->map->count(),
            'by_destination' => (clone $query)->get()->groupBy('destination')->map->count(),
        ];

        $details = $query->orderBy('lay_date', 'desc')->paginate(20);

        return response()->json([
            'summary' => $summary,
            'details' => $details,
        ]);
    }

    public function vaccination(Request $request)
    {
        return $this->sanitary($request);
    }

    public function financial(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());

        $orders = EggOrder::with('category')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->orderBy('order_date', 'desc')
            ->get();

        $summary = [
            'total_orders' => $orders->count(),
            'shipped_orders' => $orders->where('status', 'shipped')->count(),
            'total_revenue' => $orders->where('status', 'shipped')->sum(fn ($o) => $o->quantity_dozens * ($o->unit_price ?? 0)),
            'pending_orders' => $orders->whereIn('status', ['pending', 'approved', 'picked'])->count(),
        ];

        return response()->json([
            'summary' => $summary,
            'orders' => $orders,
        ]);
    }

    public function exportExcel(Request $request, string $report)
    {
        $data = $this->getReportData($report, $request);

        if ($data === null) {
            return response()->json(['message' => 'Relatório inválido'], 404);
        }

        return response()->json([
            'report' => $report,
            'exported_at' => now(),
            'filters' => $request->only(['start_date', 'end_date', 'flock_id', 'status', 'house_id']),
            'data' => $data,
        ]);
    }

    public function exportPdf(Request $request, string $report, string $format = 'json')
    {
        return $this->exportExcel($request, $report);
    }

    private function getReportData(string $report, Request $request): mixed
    {
        return match ($report) {
            'daily-production' => $this->dailyProduction($request)->getData(true),
            'rejects' => $this->rejects($request)->getData(true),
            'inventory' => $this->inventory($request)->getData(true),
            'sanitary' => $this->sanitary($request)->getData(true),
            'traceability' => $this->traceability($request)->getData(true),
            'vaccination' => $this->vaccination($request)->getData(true),
            'financial' => $this->financial($request)->getData(true),
            default => null,
        };
    }
}
