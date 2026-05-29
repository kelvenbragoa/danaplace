<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductImport;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\StockCenter;
use App\Models\StockCenterProduct;
use App\Models\TaxIva;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $products = Product::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('brand')
            ->with('category')
            ->with('iva')
            ->orderBy('name','asc')
            ->paginate();

            return $products;
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

     public function excel(Request $request){
        // $data = $request->all();
        // $files = $request->file('image');
        // // $fileContents = file($files->getPathname());

        // foreach ($files as $line) {
        //     $data = str_getcsv($line);
        //     Product::create([
        //         'name' => $data[11],
        //         'code' => $data[0],
        //         'product_brand_id'=>15,
        //         'product_category_id'=>14,
        //         'unit_id'=>1,
        //         'tax_iva_id'=>1,
        //         'quantity'=>0,
        //         'stock_min'=>0,
        //         'unity_price'=>0,
        //         'unity_buy_price'=>0
        //     ]);
        // }
        Excel::import(new ProductImport, $request->file('image'));
    }

    public function store(Request $request)
    {
        //
        $data = $request->all();
        
        $product = Product::create($data);

        $stockcenters = StockCenter::get();

        foreach($stockcenters as $item){
            StockCenterProduct::create([
                'stock_center_id'=>$item->id,
                'product_id'=>$product->id,
                'quantity'=>0
            ]);
        }
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
        $product = Product::with('brand')->with('category')->with('iva')->find($id);

        return [
            'product'=>$product,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $product = Product::find($id);
        $brands = ProductBrand::orderBy('name','asc')->get();
        $ivas = TaxIva::orderBy('name','asc')->get();
        $categories = ProductCategory::orderBy('name','asc')->get();


        return [
            'product'=>$product,
            'brands'=>$brands,
            'ivas'=>$ivas,
            'categories'=>$categories
        ];
        


        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $product = Product::find($id);

        $product->update($data);

        // $stockcenterproducts = StockCenterProduct::where('product_id',$id)->get();




        // foreach($stockcenterproducts as $item){
        //     $item->update($data);
        // }

        return $product;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        // $product = Product::find($id);

        // $product->delete();

        // return true;
    }
}
