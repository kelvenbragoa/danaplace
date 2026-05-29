<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        //
        $searchQuery = request('query');

            $categories = ProductCategory::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('products')
            ->orderBy('name','asc')
            ->paginate();

            return $categories;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->all();
        $category = ProductCategory::create($data);
        return [
            'message'=>'success'
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $category = ProductCategory::
        with('products.brand')
        ->with('products.category')
        ->with('products.iva')
        ->find($id);

        return [
            'category'=>$category,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $category = ProductCategory::find($id);
        


        return $category;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $category = ProductCategory::find($id);

        $category->update($data);

        return $category;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $category = ProductCategory::find($id);

        $category->delete();

        return true;
    }
}
