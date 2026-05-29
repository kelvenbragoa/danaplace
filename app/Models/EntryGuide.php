<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EntryGuide extends Model
{
    use HasFactory;

    protected $fillable = [
        'guide_number',
        'destination_id',
        'guest_name',
        'guest_document',
        'guest_phone',
        'guest_email',
        'host_name',
        'host_unit',
        'purpose',
        'valid_from',
        'valid_until',
        'status',
        'qr_code_path',
        'qr_code',
        'entry_time',
        'exit_time',
        'observations',
        'created_by'
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'entry_time' => 'datetime',
        'exit_time' => 'datetime',
    ];

    protected $appends = [
        'visitor_name',
        'visitor_document', 
        'visitor_phone',
        'visitor_company',
        'host_phone',
        'specific_location',
        'qr_code'
    ];

    // Relationships
    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getIsValidAttribute()
    {
        return $this->status === 'active' && 
               Carbon::now()->between($this->valid_from, $this->valid_until);
    }

    public function getVisitorNameAttribute()
    {
        return $this->guest_name;
    }

    public function getVisitorDocumentAttribute()
    {
        return $this->guest_document;
    }

    public function getVisitorPhoneAttribute()
    {
        return $this->guest_phone;
    }

    public function getVisitorCompanyAttribute()
    {
        return $this->guest_email; // We're using email field for company
    }

    public function getHostPhoneAttribute()
    {
        return null; // This field doesn't exist in backend, return null
    }

    public function getSpecificLocationAttribute()
    {
        return $this->host_unit;
    }

    public function getQrCodeAttribute($value)
    {
        // Se temos uma URL do S3 armazenada, retorna ela
        // if ($value) {
        //     return $value;
        // }
        
        // // Fallback para o path local se existir
        // if ($this->qr_code_path) {
        //     // Remove 'public/' prefix if it exists for the asset URL
        //     $path = str_replace('public/', '', $this->qr_code_path);
        //     return asset('storage/' . $path);
        // }
        
        return $value;
    }

    public function getIsExpiredAttribute()
    {
        return Carbon::now()->isAfter($this->valid_until);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'active' => 'Ativa',
            'used' => 'Utilizada',
            'expired' => 'Expirada',
            'cancelled' => 'Cancelada'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('status', 'active')
                    ->where('valid_from', '<=', Carbon::now())
                    ->where('valid_until', '>=', Carbon::now());
    }

    public function scopeByDestination($query, $destinationId)
    {
        return $query->where('destination_id', $destinationId);
    }

    // Static methods
    public static function generateGuideNumber()
    {
        $prefix = 'GE';
        $date = Carbon::now()->format('Ymd');
        $sequence = static::whereDate('created_at', Carbon::today())->count() + 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
