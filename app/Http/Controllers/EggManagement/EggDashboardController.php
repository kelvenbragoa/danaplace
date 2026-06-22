<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\DailyProduction;
use App\Models\EggModule\EggAlert;
use App\Models\EggModule\EggInventory;
use App\Models\EggModule\EggOrder;
use App\Models\EggModule\Flock;
use App\Models\EggModule\Mortality;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EggDashboardController extends Controller
{
    //
    public function index()
    {
        $stats = [
            'summary' => $this->getSummaryStats(),
            'production_today' => $this->getTodayProduction(),
            'mortality_today' => $this->getTodayMortality(),
            'inventory_status' => $this->getInventoryStatus(),
            'pending_orders' => $this->getPendingOrders(),
            'recent_alerts' => $this->getRecentAlerts()
        ];
        
        return response()->json($stats);
    }

    public function productionStats(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);
        
        $production = DailyProduction::where('date', '>=', $startDate)
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
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);
        
        $mortality = Mortality::where('date', '>=', $startDate)
            ->selectRaw('
                DATE(date) as date,
                SUM(quantity) as total_mortality
            ')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        return response()->json($mortality);
    }

    public function inventoryStats()
    {
        $inventory = EggInventory::where('status', 'available')
            ->with('egg.category')
            ->get()
            ->groupBy('egg.category_id')
            ->map(function($items) {
                return [
                    'category' => $items->first()->egg->category->name ?? 'Unknown',
                    'quantity' => $items->sum('quantity'),
                    'value' => $items->sum('quantity') * 0.50 // Assuming $0.50 per egg
                ];
            })
            ->values();
        
        return response()->json($inventory);
    }

    public function financialStats(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonth());
        $endDate = $request->get('end_date', Carbon::now());
        
        $orders = EggOrder::whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'shipped')
            ->get();
        
        $stats = [
            'total_revenue' => $orders->sum(function($order) {
                return $order->quantity_dozens * ($order->unit_price ?? 0);
            }),
            'total_orders' => $orders->count(),
            'average_order_value' => $orders->avg(function($order) {
                return $order->quantity_dozens * ($order->unit_price ?? 0);
            }),
            'top_customers' => $orders->groupBy('customer_name')->map(function($items) {
                return $items->sum(function($order) {
                    return $order->quantity_dozens * ($order->unit_price ?? 0);
                });
            })->sortDesc()->take(5)
        ];
        
        return response()->json($stats);
    }

    public function realtimeAlerts()
    {
        $alerts = [];
        
        // Check for sudden drop in production (>5% in 2 days)
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
                            'message' => "Production dropped by {$drop}% in the last 24 hours"
                        ];
                    }
                }
            }
        }
        
        // Check for high mortality
        $highMortality = Mortality::where('date', Carbon::today())
            ->where('quantity', '>', 10)
            ->with('flock')
            ->get();
        
        foreach ($highMortality as $mortality) {
            $alerts[] = [
                'type' => 'high_mortality',
                'flock_id' => $mortality->flock_id,
                'quantity' => $mortality->quantity,
                'message' => "High mortality recorded: {$mortality->quantity} birds died today"
            ];
        }
        
        return response()->json($alerts);
    }

    private function getSummaryStats()
    {
        return [
            'total_flocks' => Flock::count(),
            'active_flocks' => Flock::where('status', 'laying')->count(),
            'total_eggs_today' => DailyProduction::where('date', Carbon::today())->sum('total_eggs'),
            'total_mortality_today' => Mortality::where('date', Carbon::today())->sum('quantity'),
            'available_inventory' => EggInventory::where('status', 'available')->sum('quantity'),
            'pending_orders' => EggOrder::where('status', 'pending')->count(),
            'total_eggs_pending_orders' => EggOrder::where('status', 'pending')->sum('quantity_dozens')
        ];
    }

    private function getTodayProduction()
    {
        return DailyProduction::where('date', Carbon::today())
            ->with('flock')
            ->get()
            ->map(function ($item) {
                $birds = $item->flock->current_bird_count ?? 0;
                $layingRate = $birds > 0 ? round(($item->total_eggs / $birds) * 100, 2) : 0;

                return [
                    'flock_code' => $item->flock->code,
                    'total_eggs' => $item->total_eggs,
                    'cracked' => $item->cracked_eggs,
                    'dirty' => $item->dirty_eggs,
                    'deformed' => $item->deformed_eggs,
                    'laying_rate' => $layingRate,
                ];
            });
    }

    private function getTodayMortality()
    {
        return Mortality::where('date', Carbon::today())
            ->with('flock')
            ->get()
            ->map(function($item) {
                return [
                    'flock_code' => $item->flock->code,
                    'quantity' => $item->quantity,
                    'cause' => $item->probable_cause
                ];
            });
    }

    private function getInventoryStatus()
    {
        $inventory = EggInventory::where('status', 'available')
            ->with('egg.category')
            ->get()
            ->groupBy('egg.category_id')
            ->map(function($items) {
                return [
                    'category' => $items->first()->egg->category->name ?? 'Unknown',
                    'quantity' => $items->sum('quantity')
                ];
            })
            ->values();
        
        return $inventory;
    }

    private function getPendingOrders()
    {
        return EggOrder::whereIn('status', ['pending', 'approved'])
            ->with('category')
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
}
