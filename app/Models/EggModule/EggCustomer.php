<?php

namespace App\Models\EggModule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EggCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tax_id',
        'email',
        'phone',
        'address',
        'portal_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (EggCustomer $customer) {
            if (empty($customer->portal_code)) {
                $customer->portal_code = self::generatePortalCode();
            }
        });
    }

    public static function generatePortalCode(): string
    {
        do {
            $code = 'OVOS-' . strtoupper(Str::random(8));
        } while (self::where('portal_code', $code)->exists());

        return $code;
    }

    public function orders()
    {
        return $this->hasMany(EggOrder::class, 'customer_id');
    }

    public function regeneratePortalCode(): string
    {
        $this->portal_code = self::generatePortalCode();
        $this->save();

        return $this->portal_code;
    }
}
