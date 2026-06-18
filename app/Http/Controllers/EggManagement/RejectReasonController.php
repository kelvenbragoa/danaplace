<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\RejectReason;
use Illuminate\Http\Request;

class RejectReasonController extends Controller
{
    public function index()
    {
        $searchQuery = request('query');

        $reasons = RejectReason::query()
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('name', 'like', "%{$searchQuery}%")
                        ->orWhere('description', 'like', "%{$searchQuery}%");
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return response()->json($reasons);
    }

    public function getAll()
    {
        $reasons = RejectReason::where('is_active', true)->orderBy('name')->get();

        return response()->json($reasons);
    }

    public function show(RejectReason $rejectReason)
    {
        return response()->json($rejectReason);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:reject_reasons',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        $reason = RejectReason::create($validated);

        return response()->json($reason, 201);
    }

    public function update(Request $request, RejectReason $rejectReason)
    {
        $validated = $request->validate([
            'name' => 'string|max:100|unique:reject_reasons,name,' . $rejectReason->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['is_active'])) {
            $validated['is_active'] = (bool) $validated['is_active'];
        }

        $rejectReason->update($validated);

        return response()->json($rejectReason);
    }

    public function destroy(RejectReason $rejectReason)
    {
        $rejectReason->delete();

        return response()->json(['message' => 'Reject reason deleted successfully']);
    }

    public function toggleStatus(RejectReason $rejectReason)
    {
        $rejectReason->update(['is_active' => !$rejectReason->is_active]);

        return response()->json($rejectReason);
    }
}
