<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductBrand;
use Illuminate\Http\Request;

class ProductBrandController extends Controller
{
    public function index()
    {
        //
        $searchQuery = request('query');

            $brands = ProductBrand::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('products')
            ->orderBy('name','asc')
            ->paginate();

            return $brands;
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
        $brand = ProductBrand::create($data);
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
        $brand = ProductBrand::
        with('products.brand')
        ->with('products.category')
        ->with('products.iva')
        ->find($id);

      


        return [
            'brand'=>$brand,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $brand = ProductBrand::find($id);
        


        return $brand;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $brand = ProductBrand::find($id);

        $brand->update($data);

        return $brand;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $brand = ProductBrand::find($id);

        $brand->delete();

        return true;
    }
}
