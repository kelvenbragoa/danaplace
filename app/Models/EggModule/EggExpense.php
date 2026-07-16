<?php

namespace App\Models\EggModule;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EggExpense extends Model
{
    use HasFactory;

    // protected $table = 'egg_expenses';

    public const CATEGORY_FEED = 'feed';
    public const CATEGORY_VACCINE = 'vaccine';
    public const CATEGORY_MEDICATION = 'medication';
    public const CATEGORY_LABOR = 'labor';
    public const CATEGORY_ENERGY = 'energy';
    public const CATEGORY_PACKAGING = 'packaging';
    public const CATEGORY_MAINTENANCE = 'maintenance';
    public const CATEGORY_EQUIPMENT = 'equipment';
    public const CATEGORY_TRANSPORT = 'transport';
    public const CATEGORY_OTHER = 'other';

    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_BANK_TRANSFER = 'bank_transfer';
    public const PAYMENT_CHECK = 'check';
    public const PAYMENT_CARD = 'card';
    public const PAYMENT_OTHER = 'other';

    protected $fillable = [
        'title',
        'description',
        'amount',
        'expense_date',
        'category',
        'farm_id',
        'house_id',
        'flock_id',
        'vendor_name',
        'invoice_number',
        'payment_method',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    protected $appends = [
        'category_label',
        'payment_method_label',
    ];

    public static function categories(): array
    {
        return [
            self::CATEGORY_FEED => 'Ração',
            self::CATEGORY_VACCINE => 'Vacinas',
            self::CATEGORY_MEDICATION => 'Medicamentos',
            self::CATEGORY_LABOR => 'Mão de Obra',
            self::CATEGORY_ENERGY => 'Energia',
            self::CATEGORY_PACKAGING => 'Embalagem',
            self::CATEGORY_MAINTENANCE => 'Manutenção',
            self::CATEGORY_EQUIPMENT => 'Equipamento',
            self::CATEGORY_TRANSPORT => 'Transporte',
            self::CATEGORY_OTHER => 'Outro',
        ];
    }

    public static function paymentMethods(): array
    {
        return [
            self::PAYMENT_CASH => 'Dinheiro',
            self::PAYMENT_BANK_TRANSFER => 'Transferência Bancária',
            self::PAYMENT_CHECK => 'Cheque',
            self::PAYMENT_CARD => 'Cartão',
            self::PAYMENT_OTHER => 'Outro',
        ];
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function flock()
    {
        return $this->belongsTo(Flock::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::categories()[$this->category] ?? $this->category;
    }

    public function getPaymentMethodLabelAttribute(): ?string
    {
        if (!$this->payment_method) {
            return null;
        }

        return self::paymentMethods()[$this->payment_method] ?? $this->payment_method;
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByFarm($query, int $farmId)
    {
        return $query->where('farm_id', $farmId);
    }

    public function scopeByFlock($query, int $flockId)
    {
        return $query->where('flock_id', $flockId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }
}
