<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EggModule\Flock;

class FlockController extends Controller
{
    public function getAll()
    {
        $flocks = Flock::with('house', 'lineage')
            ->where('status', 'laying')
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $flocks,
        ]);
    }

    public function getActive()
    {
        $flocks = Flock::with('house.farm', 'lineage')
            ->whereIn('status', ['growing', 'laying'])
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $flocks,
        ]);
    }
}
