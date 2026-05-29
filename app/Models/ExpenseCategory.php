<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    /**
     * Relacionamento com despesas
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Scope para categorias ativas
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Obter total de despesas da categoria
     */
    public function getTotalExpensesAttribute()
    {
        return $this->expenses()->sum('amount');
    }

    /**
     * Obter contagem de despesas da categoria
     */
    public function getExpensesCountAttribute()
    {
        return $this->expenses()->count();
    }

    /**
     * Obter total de despesas da categoria por mês
     */
    public function getTotalByMonth($year, $month)
    {
        return $this->expenses()
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->sum('amount');
    }

    /**
     * Obter total de despesas da categoria por ano
     */
    public function getTotalByYear($year)
    {
        return $this->expenses()
            ->whereYear('expense_date', $year)
            ->sum('amount');
    }
}