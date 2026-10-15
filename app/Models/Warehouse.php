<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = [
        'tenant_id', 'code', 'name', 'type', 'address', 'city', 'state',
        'pincode', 'country', 'latitude', 'longitude',
        'manager_name', 'manager_phone', 'manager_email',
        'total_area_sqft', 'pallet_capacity', 'shelf_capacity',
        'cold_capacity', 'hazmat_capacity', 'max_weight_tons', 'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'total_area_sqft' => 'integer',
        'pallet_capacity' => 'integer',
        'shelf_capacity' => 'integer',
        'cold_capacity' => 'integer',
        'hazmat_capacity' => 'integer',
        'max_weight_tons' => 'decimal:2',
    ];

    // ==================== RELATIONSHIPS ====================
    
    public function tenant(): BelongsTo 
    { 
        return $this->belongsTo(Tenant::class); 
    }

    // ✅ THIS IS THE MISSING METHOD THAT FIXES THE ERROR
    public function bins(): HasMany 
    { 
        return $this->hasMany(WarehouseBin::class); 
    }

    public function batches(): HasMany 
    { 
        return $this->hasMany(Batch::class); 
    }

    // ==================== ACCESSORS ====================

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'main' => 'bi-building',
            'branch' => 'bi-buildings',
            'cold_storage' => 'bi-snow',
            'hazmat' => 'bi-exclamation-diamond',
            'return' => 'bi-arrow-counterclockwise',
            default => 'bi-building',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'main' => 'primary',
            'branch' => 'info',
            'cold_storage' => 'cyan',
            'hazmat' => 'danger',
            'return' => 'warning',
            default => 'secondary',
        };
    }

    public function getTotalCapacityAttribute(): int
    {
        return $this->pallet_capacity + $this->shelf_capacity + $this->cold_capacity + $this->hazmat_capacity;
    }

    public function getUsedCapacityAttribute(): int
    {
        return $this->bins()->sum('current_qty');
    }

    public function getUtilizationPercentAttribute(): float
    {
        return $this->total_capacity > 0 
            ? round(($this->used_capacity / $this->total_capacity) * 100, 1) 
            : 0;
    }

    public function getStockValueAttribute(): float
    {
        return $this->batches()
            ->join('products', 'batches.product_id', '=', 'products.id')
            ->where('batches.tenant_id', $this->tenant_id)
            ->sum(\DB::raw('batches.current_qty * products.cost_price'));
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->pincode,
            $this->country,
        ]);
        return implode(', ', $parts) ?: 'Address not set';
    }
}