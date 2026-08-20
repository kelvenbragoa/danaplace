<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\DailyProduction;
use App\Models\EggModule\EggAlert;
use App\Models\EggModule\EggExpense;
use App\Models\EggModule\EggInventory;
use App\Models\EggModule\EggOrder;
use App\Models\EggModule\EggShipping;
use App\Models\EggModule\Farm;
use App\Models\EggModule\Flock;
use App\Models\EggModule\Mortality;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EggDashboardController extends Controller
{
    public function index(Request $request)
    {
        [$startDate, $endDate] = $this->resolvePeriod($request);

        $stats = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => $this->getSummaryStats($request, $startDate, $endDate),
            'production_today' => $this->getTodayProduction($request),
            'mortality_today' => $this->getTodayMortality($request),
            'inventory_status' => $this->getInventoryStatus($request),
            'pending_orders' => $this->getPendingOrders(),
            'recent_alerts' => $this->getRecentAlerts(),
        ];

        return response()->json($stats);
    }

    /**
     * Endpoint único para gráficos + KPIs do período filtrado.
     */
    public function overview(Request $request)
    {
        [$startDate, $endDate] = $this->resolvePeriod($request);

        $productionQuery = $this->scopedProduction($request)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        $mortalityQuery = $this->scopedMortality($request)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        $byDayProduction = (clone $productionQuery)
            ->selectRaw('DATE(date) as date, SUM(total_eggs) as total_eggs, SUM(clean_eggs) as clean_eggs, SUM(cracked_eggs) as cracked_eggs, SUM(dirty_eggs) as dirty_eggs, SUM(deformed_eggs) as deformed_eggs')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $byDayMortality = (clone $mortalityQuery)
            ->selectRaw('DATE(date) as date, SUM(quantity) as total_mortality')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $productionLabels = [];
        $productionTotals = [];
        $productionCracked = [];
        $mortalitySeries = [];

        foreach ($byDayProduction as $row) {
            $label = Carbon::parse($row->date)->format('d/m');
            $productionLabels[] = $label;
            $productionTotals[] = (int) $row->total_eggs;
            $productionCracked[] = (int) $row->cracked_eggs;
            $mortalitySeries[] = (int) ($byDayMortality->get($row->date)->total_mortality ?? 0);
        }

        // Preencher mortalidade em dias sem produção
        if ($byDayProduction->isEmpty() && $byDayMortality->isNotEmpty()) {
            foreach ($byDayMortality->sortKeys() as $date => $row) {
                $productionLabels[] = Carbon::parse($date)->format('d/m');
                $productionTotals[] = 0;
                $productionCracked[] = 0;
                $mortalitySeries[] = (int) $row->total_mortality;
            }
        }

        $qualityTotals = [
            'clean' => (int) (clone $productionQuery)->sum('clean_eggs'),
            'cracked' => (int) (clone $productionQuery)->sum('cracked_eggs'),
            'dirty' => (int) (clone $productionQuery)->sum('dirty_eggs'),
            'deformed' => (int) (clone $productionQuery)->sum('deformed_eggs'),
        ];

        $byFlock = (clone $productionQuery)
            ->join('flocks', 'flocks.id', '=', 'daily_productions.flock_id')
            ->selectRaw('flocks.id as flock_id, flocks.code as flock_code, SUM(daily_productions.total_eggs) as total_eggs')
            ->groupBy('flocks.id', 'flocks.code')
            ->orderByDesc('total_eggs')
            ->limit(10)
            ->get();

        $inventory = $this->getInventoryStatus($request);

        $ordersInPeriod = EggOrder::query()
            ->whereDate('order_date', '>=', $startDate)
            ->whereDate('order_date', '<=', $endDate)
            ->get();

        $ordersByStatus = $ordersInPeriod->groupBy('status')->map->count();

        $revenue = $ordersInPeriod
            ->whereIn('status', ['shipped', 'delivered', 'picked', 'approved'])
            ->sum(fn ($o) => $o->quantity_dozens * ($o->unit_price ?? 0));

        $shippedQty = EggShipping::query()
            ->whereDate('shipping_date', '>=', $startDate)
            ->whereDate('shipping_date', '<=', $endDate)
            ->sum('quantity_eggs');

        $expensesQuery = EggExpense::query()
            ->whereDate('expense_date', '>=', $startDate)
            ->whereDate('expense_date', '<=', $endDate)
            ->when($request->filled('farm_id'), fn ($q) => $q->where('farm_id', $request->farm_id))
            ->when($request->filled('flock_id'), fn ($q) => $q->where('flock_id', $request->flock_id));

        $expenseTotal = round((clone $expensesQuery)->sum('amount'), 2);
        $expensesByCategory = (clone $expensesQuery)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $expenseCategories = EggExpense::categories();
        $expensePieLabels = [];
        $expensePieData = [];
        foreach ($expensesByCategory as $key => $total) {
            if ((float) $total <= 0) {
                continue;
            }
            $expensePieLabels[] = $expenseCategories[$key] ?? $key;
            $expensePieData[] = round((float) $total, 2);
        }

        $totalEggsPeriod = (int) (clone $productionQuery)->sum('total_eggs');
        $totalMortalityPeriod = (int) (clone $mortalityQuery)->sum('quantity');

        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'kpis' => [
                'total_eggs' => $totalEggsPeriod,
                'total_mortality' => $totalMortalityPeriod,
                'available_inventory' => EggInventory::where('status', 'available')
                    ->when($request->filled('farm_id'), function ($q) use ($request) {
                        $q->whereHas('house', fn ($h) => $h->where('farm_id', $request->farm_id));
                    })
                    ->sum('quantity'),
                'pending_orders' => EggOrder::whereIn('status', ['pending', 'approved'])->count(),
                'revenue' => round((float) $revenue, 2),
                'expenses' => $expenseTotal,
                'shipped_eggs' => (int) $shippedQty,
                'active_flocks' => Flock::whereIn('status', ['growing', 'laying'])
                    ->when($request->filled('farm_id'), function ($q) use ($request) {
                        $q->whereHas('house', fn ($h) => $h->where('farm_id', $request->farm_id));
                    })
                    ->when($request->filled('flock_id'), fn ($q) => $q->where('id', $request->flock_id))
                    ->count(),
            ],
            'charts' => [
                'production_bar' => [
                    'labels' => $productionLabels,
                    'totals' => $productionTotals,
                    'cracked' => $productionCracked,
                    'mortality' => $mortalitySeries,
                ],
                'quality_pie' => [
                    'labels' => ['Limpos', 'Rachados', 'Sujos', 'Deformados'],
                    'data' => [
                        $qualityTotals['clean'],
                        $qualityTotals['cracked'],
                        $qualityTotals['dirty'],
                        $qualityTotals['deformed'],
                    ],
                ],
                'flock_bar' => [
                    'labels' => $byFlock->pluck('flock_code'),
                    'data' => $byFlock->pluck('total_eggs')->map(fn ($v) => (int) $v),
                ],
                'inventory_pie' => [
                    'labels' => collect($inventory)->pluck('category'),
                    'data' => collect($inventory)->pluck('quantity')->map(fn ($v) => (int) $v),
                ],
                'orders_pie' => [
                    'labels' => $ordersByStatus->keys()->map(fn ($s) => $this->orderStatusLabel($s))->values(),
                    'data' => $ordersByStatus->values()->map(fn ($v) => (int) $v)->values(),
                ],
                'expenses_pie' => [
                    'labels' => $expensePieLabels,
                    'data' => $expensePieData,
                ],
            ],
            'tables' => [
                'top_flocks' => $byFlock,
                'inventory' => $inventory,
                'pending_orders' => $this->getPendingOrders(),
                'alerts' => $this->getRecentAlerts(),
            ],
        ]);
    }

    public function productionStats(Request $request)
    {
        [$startDate, $endDate] = $this->resolvePeriod($request);

        $production = $this->scopedProduction($request)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->selectRaw('
                DATE(date) as date,
                SUM(total_eggs) as total_eggs,
                SUM(cracked_eggs) as cracked_eggs,
                SUM(dirty_eggs) as dirty_eggs,
                SUM(deformed_eggs) as deformed_eggs
            ')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($production);
    }

    public function mortalityStats(Request $request)
    {
        [$startDate, $endDate] = $this->resolvePeriod($request);

        $mortality = $this->scopedMortality($request)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->selectRaw('DATE(date) as date, SUM(quantity) as total_mortality')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($mortality);
    }

    public function inventoryStats(Request $request)
    {
        return response()->json($this->getInventoryStatus($request));
    }

    public function financialStats(Request $request)
    {
        [$startDate, $endDate] = $this->resolvePeriod($request);

        $orders = EggOrder::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['shipped', 'delivered', 'picked', 'approved'])
            ->get();

        $stats = [
            'total_revenue' => $orders->sum(fn ($order) => $order->quantity_dozens * ($order->unit_price ?? 0)),
            'total_orders' => $orders->count(),
            'average_order_value' => $orders->avg(fn ($order) => $order->quantity_dozens * ($order->unit_price ?? 0)),
            'top_customers' => $orders->groupBy('customer_name')->map(function ($items) {
                return $items->sum(fn ($order) => $order->quantity_dozens * ($order->unit_price ?? 0));
            })->sortDesc()->take(5),
        ];

        return response()->json($stats);
    }

    public function realtimeAlerts()
    {
        $alerts = [];

        $productionData = DailyProduction::where('date', '>=', Carbon::now()->subDays(2))
            ->selectRaw('flock_id, DATE(date) as date, SUM(total_eggs) as total')
            ->groupBy('flock_id', 'date')
            ->get()
            ->groupBy('flock_id');

        foreach ($productionData as $flockId => $data) {
            if ($data->count() >= 2) {
                $days = $data->sortByDesc('date')->values();
                $today = $days[0]->total;
                $yesterday = $days[1]->total;

                if ($yesterday > 0) {
                    $drop = (($yesterday - $today) / $yesterday) * 100;
                    if ($drop > 5) {
                        $alerts[] = [
                            'type' => 'production_drop',
                            'flock_id' => $flockId,
                            'drop_percentage' => round($drop, 2),
                            'message' => 'Queda de produção de ' . round($drop, 1) . '% nas últimas 24h',
                        ];
                    }
                }
            }
        }

        $highMortality = Mortality::where('date', Carbon::today())
            ->where('quantity', '>', 10)
            ->with('flock')
            ->get();

        foreach ($highMortality as $mortality) {
            $alerts[] = [
                'type' => 'high_mortality',
                'flock_id' => $mortality->flock_id,
                'quantity' => $mortality->quantity,
                'message' => 'Mortalidade elevada: ' . $mortality->quantity . ' aves hoje (' . ($mortality->flock->code ?? '') . ')',
            ];
        }

        return response()->json($alerts);
    }

    private function resolvePeriod(Request $request): array
    {
        if ($request->filled('start_date') && $request->filled('end_date')) {
            return [
                Carbon::parse($request->start_date)->toDateString(),
                Carbon::parse($request->end_date)->toDateString(),
            ];
        }

        $period = $request->get('period', '30');
        $end = Carbon::now()->toDateString();

        return match ($period) {
            '7', 'week' => [Carbon::now()->subDays(6)->toDateString(), $end],
            '14' => [Carbon::now()->subDays(13)->toDateString(), $end],
            '90' => [Carbon::now()->subDays(89)->toDateString(), $end],
            'year', 'yeartodate' => [Carbon::now()->startOfYear()->toDateString(), $end],
            'month', 'monthtodate' => [Carbon::now()->startOfMonth()->toDateString(), $end],
            default => [Carbon::now()->subDays(29)->toDateString(), $end],
        };
    }

    private function scopedProduction(Request $request)
    {
        return DailyProduction::query()
            ->when($request->filled('flock_id'), fn ($q) => $q->where('flock_id', $request->flock_id))
            ->when($request->filled('farm_id'), function ($q) use ($request) {
                $q->whereHas('flock.house', fn ($h) => $h->where('farm_id', $request->farm_id));
            });
    }

    private function scopedMortality(Request $request)
    {
        return Mortality::query()
            ->when($request->filled('flock_id'), fn ($q) => $q->where('flock_id', $request->flock_id))
            ->when($request->filled('farm_id'), function ($q) use ($request) {
                $q->whereHas('flock.house', fn ($h) => $h->where('farm_id', $request->farm_id));
            });
    }

    private function getSummaryStats(Request $request, string $startDate, string $endDate)
    {
        $production = $this->scopedProduction($request)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        return [
            'total_flocks' => Flock::count(),
            'active_flocks' => Flock::whereIn('status', ['growing', 'laying'])->count(),
            'total_eggs_today' => DailyProduction::whereDate('date', Carbon::today())->sum('total_eggs'),
            'total_eggs_period' => (int) (clone $production)->sum('total_eggs'),
            'total_mortality_today' => Mortality::whereDate('date', Carbon::today())->sum('quantity'),
            'available_inventory' => EggInventory::where('status', 'available')->sum('quantity'),
            'pending_orders' => EggOrder::where('status', 'pending')->count(),
            'total_eggs_pending_orders' => EggOrder::where('status', 'pending')->sum('quantity_dozens'),
        ];
    }

    private function getTodayProduction(Request $request)
    {
        return $this->scopedProduction($request)
            ->whereDate('date', Carbon::today())
            ->with('flock')
            ->get()
            ->map(function ($item) {
                $birds = $item->flock->current_bird_count ?? 0;
                $layingRate = $birds > 0 ? round(($item->total_eggs / $birds) * 100, 2) : 0;

                return [
                    'flock_code' => $item->flock->code ?? '-',
                    'total_eggs' => $item->total_eggs,
                    'cracked' => $item->cracked_eggs,
                    'dirty' => $item->dirty_eggs,
                    'deformed' => $item->deformed_eggs,
                    'laying_rate' => $layingRate,
                ];
            });
    }

    private function getTodayMortality(Request $request)
    {
        return $this->scopedMortality($request)
            ->whereDate('date', Carbon::today())
            ->with('flock')
            ->get()
            ->map(function ($item) {
                return [
                    'flock_code' => $item->flock->code ?? '-',
                    'quantity' => $item->quantity,
                    'cause' => $item->probable_cause,
                ];
            });
    }

    private function getInventoryStatus(Request $request)
    {
        return EggInventory::where('status', 'available')
            ->when($request->filled('farm_id'), function ($q) use ($request) {
                $q->whereHas('house', fn ($h) => $h->where('farm_id', $request->farm_id));
            })
            ->with('egg.category')
            ->get()
            ->groupBy('egg.category_id')
            ->map(function ($items) {
                return [
                    'category' => $items->first()->egg->category->name ?? 'Sem categoria',
                    'quantity' => $items->sum('quantity'),
                ];
            })
            ->values();
    }

    private function getPendingOrders()
    {
        return EggOrder::whereIn('status', ['pending', 'approved'])
            ->with('category')
            ->orderByDesc('order_date')
            ->limit(10)
            ->get();
    }

    private function getRecentAlerts()
    {
        return EggAlert::with('flock')
            ->where('status', '!=', 'resolved')
            ->orderBy('alert_datetime', 'desc')
            ->limit(5)
            ->get();
    }

    private function orderStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'picked' => 'Separado',
            'shipped' => 'Expedido',
            'delivered' => 'Entregue',
            'canceled' => 'Cancelado',
            default => $status,
        };
    }
}
