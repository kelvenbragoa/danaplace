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
        $groupBy = $request->get('group_by', 'day') === 'month' ? 'month' : 'day';

        $productionQuery = $this->scopedProduction($request)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        $mortalityQuery = $this->scopedMortality($request)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        $productionSeries = $this->buildProductionSeries($productionQuery, $mortalityQuery, $groupBy);
        $feedSeries = $this->buildFeedSeries($productionQuery, $groupBy);
        $sizeSeries = $this->buildSizeSeries($productionQuery, $groupBy);
        $ordersSeries = $this->buildOrdersShippingSeries($startDate, $endDate, $groupBy);

        $qualityTotals = [
            'clean' => (int) (clone $productionQuery)->sum('clean_eggs'),
            'cracked' => (int) (clone $productionQuery)->sum('cracked_eggs'),
            'dirty' => (int) (clone $productionQuery)->sum('dirty_eggs'),
            'deformed' => (int) (clone $productionQuery)->sum('deformed_eggs'),
        ];

        $sizeTotals = [
            'normal' => (int) (clone $productionQuery)->sum('normal_eggs'),
            'grande' => (int) (clone $productionQuery)->sum('grande_eggs'),
            'jumbo' => (int) (clone $productionQuery)->sum('jumbo_eggs'),
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

        $totalFeedKg = round((float) (clone $productionQuery)->sum('feed_consumption_kg'), 2);
        $feedPricePerKg = $this->feedPricePerKg();
        $totalFeedCost = round($totalFeedKg * $feedPricePerKg, 2);
        $totalFeedBags = config('egg.feed_bag_kg') > 0
            ? round($totalFeedKg / config('egg.feed_bag_kg'), 2)
            : 0;

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
                'group_by' => $groupBy,
            ],
            'feed_config' => [
                'bag_kg' => config('egg.feed_bag_kg'),
                'bag_price_mzn' => config('egg.feed_bag_price_mzn'),
                'price_per_kg' => $feedPricePerKg,
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
                'total_feed_kg' => $totalFeedKg,
                'total_feed_cost_mzn' => $totalFeedCost,
                'total_feed_bags' => $totalFeedBags,
                'active_flocks' => Flock::whereIn('status', ['growing', 'laying'])
                    ->when($request->filled('farm_id'), function ($q) use ($request) {
                        $q->whereHas('house', fn ($h) => $h->where('farm_id', $request->farm_id));
                    })
                    ->when($request->filled('flock_id'), fn ($q) => $q->where('id', $request->flock_id))
                    ->count(),
            ],
            'charts' => [
                'production_bar' => $productionSeries,
                'size_bar' => $sizeSeries,
                'feed_bar' => $feedSeries,
                'orders_bar' => $ordersSeries,
                'size_totals_bar' => [
                    'labels' => ['Normal', 'Grande', 'Jumbo'],
                    'data' => [$sizeTotals['normal'], $sizeTotals['grande'], $sizeTotals['jumbo']],
                ],
                'inventory_bar' => [
                    'labels' => collect($inventory)->pluck('category'),
                    'data' => collect($inventory)->pluck('quantity')->map(fn ($v) => (int) $v),
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

    private function feedPricePerKg(): float
    {
        $bagKg = (float) config('egg.feed_bag_kg', 50);
        $bagPrice = (float) config('egg.feed_bag_price_mzn', 1800);

        return $bagKg > 0 ? round($bagPrice / $bagKg, 4) : 0;
    }

    private function periodGroupExpression(string $groupBy, string $column = 'date'): string
    {
        return $groupBy === 'month'
            ? "DATE_FORMAT({$column}, '%Y-%m')"
            : "DATE({$column})";
    }

    private function formatPeriodLabel(string $bucket, string $groupBy): string
    {
        if ($groupBy === 'month') {
            return Carbon::createFromFormat('Y-m', $bucket)->format('m/Y');
        }

        return Carbon::parse($bucket)->format('d/m');
    }

    private function buildProductionSeries($productionQuery, $mortalityQuery, string $groupBy): array
    {
        $groupExpr = $this->periodGroupExpression($groupBy);

        $byPeriod = (clone $productionQuery)
            ->selectRaw("{$groupExpr} as period_key, SUM(total_eggs) as total_eggs, SUM(cracked_eggs) as cracked_eggs")
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get()
            ->keyBy('period_key');

        $byMortality = (clone $mortalityQuery)
            ->selectRaw("{$groupExpr} as period_key, SUM(quantity) as total_mortality")
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get()
            ->keyBy('period_key');

        $allKeys = $byPeriod->keys()
            ->merge($byMortality->keys())
            ->unique()
            ->sort()
            ->values();

        $labels = [];
        $totals = [];
        $cracked = [];
        $mortality = [];

        foreach ($allKeys as $key) {
            $labels[] = $this->formatPeriodLabel((string) $key, $groupBy);
            $totals[] = (int) ($byPeriod->get($key)->total_eggs ?? 0);
            $cracked[] = (int) ($byPeriod->get($key)->cracked_eggs ?? 0);
            $mortality[] = (int) ($byMortality->get($key)->total_mortality ?? 0);
        }

        return compact('labels', 'totals', 'cracked', 'mortality');
    }

    private function buildFeedSeries($productionQuery, string $groupBy): array
    {
        $groupExpr = $this->periodGroupExpression($groupBy);
        $pricePerKg = $this->feedPricePerKg();

        $rows = (clone $productionQuery)
            ->selectRaw("{$groupExpr} as period_key, SUM(feed_consumption_kg) as feed_kg")
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get();

        $labels = [];
        $kg = [];
        $cost_mzn = [];
        $bags = [];
        $bagKg = (float) config('egg.feed_bag_kg', 50);

        foreach ($rows as $row) {
            $feedKg = round((float) $row->feed_kg, 2);
            $labels[] = $this->formatPeriodLabel((string) $row->period_key, $groupBy);
            $kg[] = $feedKg;
            $cost_mzn[] = round($feedKg * $pricePerKg, 2);
            $bags[] = $bagKg > 0 ? round($feedKg / $bagKg, 2) : 0;
        }

        return compact('labels', 'kg', 'cost_mzn', 'bags');
    }

    private function buildSizeSeries($productionQuery, string $groupBy): array
    {
        $groupExpr = $this->periodGroupExpression($groupBy);

        $rows = (clone $productionQuery)
            ->selectRaw("{$groupExpr} as period_key, SUM(normal_eggs) as normal_eggs, SUM(grande_eggs) as grande_eggs, SUM(jumbo_eggs) as jumbo_eggs")
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get();

        $labels = [];
        $normal = [];
        $grande = [];
        $jumbo = [];

        foreach ($rows as $row) {
            $labels[] = $this->formatPeriodLabel((string) $row->period_key, $groupBy);
            $normal[] = (int) $row->normal_eggs;
            $grande[] = (int) $row->grande_eggs;
            $jumbo[] = (int) $row->jumbo_eggs;
        }

        return compact('labels', 'normal', 'grande', 'jumbo');
    }

    private function buildOrdersShippingSeries(string $startDate, string $endDate, string $groupBy): array
    {
        $orderGroup = $this->periodGroupExpression($groupBy, 'order_date');
        $shipGroup = $this->periodGroupExpression($groupBy, 'shipping_date');
        $schedGroup = $this->periodGroupExpression($groupBy, 'expected_delivery_date');

        $ordersByPeriod = EggOrder::query()
            ->whereDate('order_date', '>=', $startDate)
            ->whereDate('order_date', '<=', $endDate)
            ->selectRaw("{$orderGroup} as period_key, COUNT(*) as order_count, SUM(quantity_dozens) as order_qty")
            ->groupBy('period_key')
            ->get()
            ->keyBy('period_key');

        $shippedByPeriod = EggShipping::query()
            ->whereDate('shipping_date', '>=', $startDate)
            ->whereDate('shipping_date', '<=', $endDate)
            ->selectRaw("{$shipGroup} as period_key, COUNT(*) as ship_count, SUM(quantity_eggs) as ship_qty")
            ->groupBy('period_key')
            ->get()
            ->keyBy('period_key');

        $scheduledByPeriod = EggOrder::query()
            ->whereNotNull('expected_delivery_date')
            ->whereDate('expected_delivery_date', '>=', $startDate)
            ->whereDate('expected_delivery_date', '<=', $endDate)
            ->whereNotIn('status', ['canceled', 'shipped'])
            ->selectRaw("{$schedGroup} as period_key, COUNT(*) as sched_count, SUM(quantity_dozens) as sched_qty")
            ->groupBy('period_key')
            ->get()
            ->keyBy('period_key');

        $allKeys = collect()
            ->merge($ordersByPeriod->keys())
            ->merge($shippedByPeriod->keys())
            ->merge($scheduledByPeriod->keys())
            ->unique()
            ->sort()
            ->values();

        $labels = [];
        $orders = [];
        $shipped = [];
        $scheduled = [];
        $order_qty = [];
        $shipped_qty = [];
        $scheduled_qty = [];

        foreach ($allKeys as $key) {
            $labels[] = $this->formatPeriodLabel((string) $key, $groupBy);
            $orders[] = (int) ($ordersByPeriod->get($key)->order_count ?? 0);
            $shipped[] = (int) ($shippedByPeriod->get($key)->ship_count ?? 0);
            $scheduled[] = (int) ($scheduledByPeriod->get($key)->sched_count ?? 0);
            $order_qty[] = (int) ($ordersByPeriod->get($key)->order_qty ?? 0);
            $shipped_qty[] = (int) ($shippedByPeriod->get($key)->ship_qty ?? 0);
            $scheduled_qty[] = (int) ($scheduledByPeriod->get($key)->sched_qty ?? 0);
        }

        return compact('labels', 'orders', 'shipped', 'scheduled', 'order_qty', 'shipped_qty', 'scheduled_qty');
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
