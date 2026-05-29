<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;

class ProductImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Product([
            //
                'name' => $row[11],
                'code' => $row[0],
                'product_brand_id'=>15,
                'product_category_id'=>14,
                'unit_id'=>1,
                'tax_iva_id'=>1,
                'quantity'=>0,
                'stock_min'=>0,
                'unity_price'=>0,
                'unity_buy_price'=>0
        ]);
    }
}
