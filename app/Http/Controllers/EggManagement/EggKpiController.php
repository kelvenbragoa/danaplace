<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\DailyProduction;
use App\Models\EggModule\Flock;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EggKpiController extends Controller
{
    //
    public function layingRate(Request $request)
    {
        $flockId = $request->get('flock_id');
        $days = $request->get('days', 30);
        
        $query = DailyProduction::with('flock')
            ->where('date', '>=', Carbon::now()->subDays($days));
        
        if ($flockId) {
            $query->where('flock_id', $flockId);
        }
        
        $data = $query->get()->map(function ($item) {
            $birds = $item->flock->current_bird_count ?? 0;
            $rate = $birds > 0 ? round(($item->total_eggs / $birds) * 100, 2) : 0;

            return [
                'date' => $item->date,
                'flock_code' => $item->flock->code,
                'laying_rate' => $rate,
                'total_eggs' => $item->total_eggs,
                'bird_count' => $birds,
            ];
        });

        $summary = [
            'average_rate' => round($data->avg('laying_rate'), 2),
            'max_rate' => $data->max('laying_rate'),
            'min_rate' => $data->min('laying_rate'),
            'data' => $data,
        ];
        
        return response()->json($summary);
    }

    public function mortalityRate(Request $request)
    {
        $flockId = $request->get('flock_id');
        
        $flocks = Flock::when($flockId, function($query, $id) {
            return $query->where('id', $id);
        })->get();
        
        $results = $flocks->map(function($flock) {
            return [
                'flock_code' => $flock->code,
                'initial_birds' => $flock->initial_bird_count,
                'current_birds' => $flock->current_bird_count,
                'total_deaths' => $flock->initial_bird_count - $flock->current_bird_count,
                'mortality_rate' => $flock->mortality_rate,
                'status' => $flock->status
            ];
        });
        
        $summary = [
            'overall_rate' => $results->avg('mortality_rate'),
            'by_flock' => $results
        ];
        
        return response()->json($summary);
    }

    public function feedConversion(Request $request)
    {
        $flockId = $request->get('flock_id');
        $days = $request->get('days', 30);
        
        $query = DailyProduction::with('flock')
            ->where('date', '>=', Carbon::now()->subDays($days));
        
        if ($flockId) {
            $query->where('flock_id', $flockId);
        }
        
        $data = $query->get()->map(function($item) {
            $conversion = $item->total_eggs > 0 
                ? $item->feed_consumption_kg / $item->total_eggs 
                : 0;
            
            return [
                'date' => $item->date,
                'flock_code' => $item->flock->code,
                'feed_consumption_kg' => $item->feed_consumption_kg,
                'total_eggs' => $item->total_eggs,
                'conversion_rate' => round($conversion, 3)
            ];
        });
        
        $summary = [
            'average_conversion' => $data->avg('conversion_rate'),
            'best_conversion' => $data->min('conversion_rate'),
            'worst_conversion' => $data->max('conversion_rate'),
            'data' => $data
        ];
        
        return response()->json($summary);
    }

    public function layingCurve(Request $request)
    {
        $flockId = $request->get('flock_id');
        
        if (!$flockId) {
            return response()->json(['error' => 'Flock ID is required'], 400);
        }
        
        $flock = Flock::with('lineage')->findOrFail($flockId);
        
        // Get actual production data
        $actual = DailyProduction::where('flock_id', $flockId)
            ->orderBy('date')
            ->get(['date', 'total_eggs']);
        
        // Generate expected curve based on lineage
        $expected = $this->generateExpectedCurve($flock);
        
        return response()->json([
            'flock_code' => $flock->code,
            'lineage' => $flock->lineage->name ?? 'Unknown',
            'actual' => $actual,
            'expected' => $expected,
            'deviation' => $this->calculateDeviation($actual, $expected)
        ]);
    }

    public function houseRanking(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonth());
        $endDate = $request->get('end_date', Carbon::now());
        
        $ranking = Flock::with('house')
            ->where('status', 'laying')
            ->get()
            ->map(function ($flock) use ($startDate, $endDate) {
                $production = DailyProduction::where('flock_id', $flock->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->sum('total_eggs');

                $birds = $flock->current_bird_count ?: 1;
                $avgLayingRate = DailyProduction::where('flock_id', $flock->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->avg('total_eggs') / $birds * 100;

                return [
                    'house_name' => $flock->house->name,
                    'flock_code' => $flock->code,
                    'total_production' => $production,
                    'avg_laying_rate' => round($avgLayingRate, 2),
                    'efficiency_score' => $this->calculateEfficiencyScore($flock),
                ];
            })
            ->sortByDesc('efficiency_score')
            ->values();
        
        return response()->json($ranking);
    }

    public function costPerDozen(Request $request)
    {
        $flockId = $request->get('flock_id');
        $period = $request->get('period', 'month'); // week, month, year
        
        $startDate = $this->getStartDateByPeriod($period);
        
        $query = DailyProduction::with('flock')
            ->where('date', '>=', $startDate);
        
        if ($flockId) {
            $query->where('flock_id', $flockId);
        }
        
        $productions = $query->get();
        
        $totalEggs = $productions->sum('total_eggs');
        $totalFeed = $productions->sum('feed_consumption_kg');
        $totalWater = $productions->sum('water_consumption_liters');
        
        // Cost assumptions (these should come from your actual cost data)
        $feedCostPerKg = 0.50;
        $waterCostPerLiter = 0.002;
        $laborCostPerDay = 50;
        $otherCosts = 100;
        
        $totalCost = ($totalFeed * $feedCostPerKg) + 
                     ($totalWater * $waterCostPerLiter) + 
                     ($laborCostPerDay * $productions->count()) + 
                     $otherCosts;
        
        $totalDozens = $totalEggs / 12;
        $costPerDozen = $totalDozens > 0 ? $totalCost / $totalDozens : 0;
        
        return response()->json([
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => Carbon::now(),
            'total_eggs' => $totalEggs,
            'total_dozens' => round($totalDozens, 2),
            'total_cost' => round($totalCost, 2),
            'cost_per_dozen' => round($costPerDozen, 2),
            'breakdown' => [
                'feed_cost' => round($totalFeed * $feedCostPerKg, 2),
                'water_cost' => round($totalWater * $waterCostPerLiter, 2),
                'labor_cost' => $laborCostPerDay * $productions->count(),
                'other_costs' => $otherCosts
            ]
        ]);
    }

    public function rejectRate(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonth());
        $endDate = $request->get('end_date', Carbon::now());
        
        $production = DailyProduction::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                SUM(total_eggs) as total_eggs,
                SUM(cracked_eggs) as cracked_eggs,
                SUM(dirty_eggs) as dirty_eggs,
                SUM(deformed_eggs) as deformed_eggs
            ')
            ->first();
        
        $totalRejects = ($production->cracked_eggs ?? 0) + 
                        ($production->dirty_eggs ?? 0) + 
                        ($production->deformed_eggs ?? 0);
        
        $rejectRate = $production->total_eggs > 0 
            ? ($totalRejects / $production->total_eggs) * 100 
            : 0;
        
        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_eggs' => $production->total_eggs ?? 0,
            'total_rejects' => $totalRejects,
            'reject_rate' => round($rejectRate, 2),
            'breakdown' => [
                'cracked' => $production->cracked_eggs ?? 0,
                'dirty' => $production->dirty_eggs ?? 0,
                'deformed' => $production->deformed_eggs ?? 0
            ]
        ]);
    }

    public function efficiencyIndex(Request $request)
    {
        $flockId = $request->get('flock_id');
        
        $flocks = Flock::when($flockId, function($query, $id) {
            return $query->where('id', $id);
        })->where('status', 'laying')->get();
        
        $results = $flocks->map(function($flock) {
            return [
                'flock_code' => $flock->code,
                'efficiency_index' => $this->calculateEfficiencyScore($flock),
                'laying_rate' => $flock->laying_rate,
                'mortality_rate' => $flock->mortality_rate,
                'age_days' => $flock->age_days
            ];
        });
        
        return response()->json([
            'average_efficiency' => $results->avg('efficiency_index'),
            'by_flock' => $results->sortByDesc('efficiency_index')->values()
        ]);
    }

    private function generateExpectedCurve(Flock $flock)
    {
        $curve = [];
        $age = $flock->age_days;
        
        for ($i = 1; $i <= 30; $i++) {
            $dayAge = $age + $i;
            $expectedRate = $this->getExpectedLayingRate($dayAge, $flock->lineage->name ?? 'Standard');
            $curve[] = [
                'date' => Carbon::now()->addDays($i)->format('Y-m-d'),
                'age_days' => $dayAge,
                'expected_eggs' => round(($expectedRate / 100) * $flock->current_bird_count)
            ];
        }
        
        return $curve;
    }

    private function getExpectedLayingRate($ageDays, $lineage)
    {
        // Simplified curve - in production, use actual lineage data
        if ($ageDays < 140) return 0;
        if ($ageDays < 160) return 50;
        if ($ageDays < 180) return 70;
        if ($ageDays < 200) return 85;
        if ($ageDays < 300) return 90;
        if ($ageDays < 400) return 80;
        return 70;
    }

    private function calculateDeviation($actual, $expected)
    {
        $actualByDate = $actual->keyBy('date');
        $deviation = [];
        
        foreach ($expected as $exp) {
            $act = $actualByDate->get($exp['date']);
            $actualValue = $act ? $act['total_eggs'] : 0;
            $expectedValue = $exp['expected_eggs'];
            
            $deviation[] = [
                'date' => $exp['date'],
                'actual' => $actualValue,
                'expected' => $expectedValue,
                'deviation' => $actualValue - $expectedValue,
                'percentage' => $expectedValue > 0 ? (($actualValue - $expectedValue) / $expectedValue) * 100 : 0
            ];
        }
        
        return $deviation;
    }

    private function calculateEfficiencyScore(Flock $flock)
    {
        $layingScore = min(100, ($flock->laying_rate / 95) * 100);
        $mortalityScore = max(0, 100 - ($flock->mortality_rate * 10));
        $ageScore = max(0, 100 - (max(0, $flock->age_days - 400) / 10));
        
        return round(($layingScore * 0.6) + ($mortalityScore * 0.3) + ($ageScore * 0.1), 2);
    }

    private function getStartDateByPeriod($period)
    {
        return match($period) {
            'week' => Carbon::now()->subWeek(),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subMonth(),
        };
    }
}
