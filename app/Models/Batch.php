<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'warehouse_id', // ✅ Added warehouse_id
        'warehouse_bin_id', 
        'batch_no',
        'mfg_date',
        'exp_date',
        'initial_qty',
        'current_qty',
        'status',
    ];

    protected $casts = [
        'mfg_date' => 'date',
        'exp_date' => 'date',
        'initial_qty' => 'integer',
        'current_qty' => 'integer',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ✅ ADD THIS: Link batch to its warehouse
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function warehouseBin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'warehouse_bin_id');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    public function grqrPostings(): HasMany
    {
        return $this->hasMany(GrqrPosting::class);
    }

    public function qualityChecks(): HasMany
    {
        return $this->hasMany(QualityCheck::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('exp_date', '<=', now()->addDays($days))
                     ->where('exp_date', '>=', now())
                     ->where('status', 'active');
    }

    // Accessors
    public function getIsExpiredAttribute(): bool
    {
        return $this->exp_date && $this->exp_date->isPast();
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->exp_date) return null;
        return now()->diffInDays($this->exp_date, false);
    }
}