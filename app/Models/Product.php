<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'product_brand_id',
        'product_category_id',
        'quantity',
        'stock_min',
        'unity_price',
        'tax_iva_id',
        'unit_id',
        'unity_buy_price'
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->code)) {
                $product->code = self::generateNextCode();
            }
        });
    }

    public static function generateNextCode(): string
    {
        $lastCode = self::where('code', 'like', 'PROD-%')
            ->orderByDesc('id')
            ->value('code');

        $next = 1;
        if ($lastCode && preg_match('/PROD-(\d+)/i', $lastCode, $matches)) {
            $next = ((int) $matches[1]) + 1;
        } else {
            $next = ((int) self::max('id')) + 1;
        }

        do {
            $code = 'PROD-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function brand()
    {
        return $this->hasOne('App\Models\ProductBrand', 'id', 'product_brand_id');
    }

    public function category()
    {
        return $this->hasOne('App\Models\ProductCategory', 'id', 'product_category_id');
    }

    public function iva()
    {
        return $this->hasOne('App\Models\TaxIva', 'id', 'tax_iva_id');
    }

    public function unity()
    {
        return $this->hasOne('App\Models\Unit', 'id', 'unit_id');
    }
}
