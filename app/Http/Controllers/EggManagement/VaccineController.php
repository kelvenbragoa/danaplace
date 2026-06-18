<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\Vaccine;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VaccineController extends Controller
{
    public function index()
    {
        $searchQuery = request('query');

        $vaccines = Vaccine::query()
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('name', 'like', "%{$searchQuery}%")
                        ->orWhere('manufacturer', 'like', "%{$searchQuery}%")
                        ->orWhere('batch', 'like', "%{$searchQuery}%");
                });
            })
            ->withCount('vaccinationSchedule')
            ->orderBy('name')
            ->paginate(15);

        return response()->json($vaccines);
    }

    public function getAll()
    {
        $vaccines = Vaccine::orderBy('name')->get();
        return response()->json($vaccines);
    }

    public function expiringSoon()
    {
        $vaccines = Vaccine::where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>', Carbon::now())
            ->orderBy('expiry_date')
            ->get();
        
        return response()->json($vaccines);
    }

    public function show(Vaccine $vaccine)
    {
        return response()->json($vaccine->load('vaccinationSchedule.flock.house'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'manufacturer' => 'required|string|max:100',
            'batch' => 'required|string|max:50|unique:vaccines',
            'expiry_date' => 'required|date',
            'min_stock' => 'integer|min:0'
        ]);

        $vaccine = Vaccine::create($validated);
        return response()->json($vaccine, 201);
    }

    public function update(Request $request, Vaccine $vaccine)
    {
        $validated = $request->validate([
            'name' => 'string|max:100',
            'manufacturer' => 'string|max:100',
            'batch' => 'string|max:50|unique:vaccines,batch,' . $vaccine->id,
            'expiry_date' => 'date',
            'min_stock' => 'integer|min:0'
        ]);

        $vaccine->update($validated);
        return response()->json($vaccine);
    }

    public function destroy(Vaccine $vaccine)
    {
        $vaccine->delete();
        return response()->json(['message' => 'Vaccine deleted successfully']);
    }

    public function adjustStock(Request $request, Vaccine $vaccine)
    {
        $request->validate(['stock' => 'required|integer|min:0']);
        // Implement stock adjustment logic if you have a stock column
        return response()->json($vaccine);
    }
}
