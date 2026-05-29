<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentFee extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Relacionamentos
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }
}
