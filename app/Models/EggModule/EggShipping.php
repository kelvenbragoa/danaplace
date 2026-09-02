<?php

namespace App\Models\EggModule;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EggShipping extends Model
{
    use HasFactory;
    // protected $table = 'egg_shipping';
    
    protected $fillable = [
        'order_id', 'inventory_id', 'quantity_eggs', 'shipping_date', 'invoice_number', 
        'carrier', 'vehicle_plate', 'driver_name', 'vehicle_temperature', 
        'seal_number', 'health_certificate', 'delivery_note_number',
        'delivered_to', 'delivered_at', 'responsible_id'
    ];

    protected $casts = [
        'shipping_date' => 'date',
        'delivered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(EggOrder::class, 'order_id');
    }

    public function inventory()
    {
        return $this->belongsTo(EggInventory::class, 'inventory_id');
    }

    public function items()
    {
        return $this->hasMany(EggShippingItem::class, 'egg_shipping_id');
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public static function generateNextInvoiceNumber(): string
    {
        $year = now()->year;
        $prefix = "FT-{$year}-";

        $lastNumber = self::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $next = 1;
        if ($lastNumber && preg_match('/-(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        do {
            $invoiceNumber = $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
            $next++;
        } while (self::where('invoice_number', $invoiceNumber)->exists());

        return $invoiceNumber;
    }
}
