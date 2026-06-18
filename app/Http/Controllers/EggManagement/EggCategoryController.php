<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\EggCategory;
use Illuminate\Http\Request;

class EggCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $searchQuery = request('query');

        $categories = EggCategory::query()
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where('name', 'like', "%{$searchQuery}%");
            })
            ->orderBy('min_weight')
            ->paginate(15);

        return response()->json($categories);
    }

    public function getAll()
    {
        $categories = EggCategory::where('is_active', true)->orderBy('min_weight')->get();
        return response()->json($categories);
    }

    public function show(EggCategory $eggCategory)
    {
        return response()->json($eggCategory);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:20',
            'min_weight' => 'required|numeric|min:0',
            'max_weight' => 'required|numeric|gt:min_weight',
            'is_active' => 'boolean'
        ]);

        $category = EggCategory::create($validated);
        return response()->json($category, 201);
    }

    public function update(Request $request, EggCategory $eggCategory)
    {
        $validated = $request->validate([
            'name' => 'string|max:20',
            'min_weight' => 'numeric|min:0',
            'max_weight' => 'numeric|gt:min_weight',
            'is_active' => 'boolean'
        ]);

        $eggCategory->update($validated);
        return response()->json($eggCategory);
    }

    public function destroy(EggCategory $eggCategory)
    {
        $eggCategory->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function toggleStatus(EggCategory $eggCategory)
    {
        $eggCategory->update(['is_active' => !$eggCategory->is_active]);
        return response()->json($eggCategory);
    }
}
