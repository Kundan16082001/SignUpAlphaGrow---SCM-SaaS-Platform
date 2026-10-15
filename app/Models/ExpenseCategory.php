<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    protected $fillable = ['tenant_id', 'name', 'icon', 'color', 'status'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function expenses(): HasMany { return $this->hasMany(Expense::class, 'category_id'); }

    public function scopeActive($query) { return $query->where('status', 'active'); }

    public function getTotalExpensesAttribute(): float
    {
        return $this->expenses()->where('status', 'paid')->sum('total');
    }
}