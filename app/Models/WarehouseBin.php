<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseBin extends Model
{
    protected $fillable = [
        'tenant_id', 'bin_code', 'type', 'zone', 'aisle', 'rack',
        'max_capacity', 'current_qty', 'temperature_zone', 'status'
    ];

    protected $casts = [
        'max_capacity' => 'integer',
        'current_qty' => 'integer',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }

    public function getUtilizationAttribute(): int
    {
        if ($this->max_capacity <= 0) return 0;
        return min(100, (int)(($this->current_qty / $this->max_capacity) * 100));
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'available' => 'success',
            'occupied' => 'primary',
            'reserved' => 'warning',
            'maintenance' => 'danger',
            default => 'secondary',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'pallet' => 'bi-palette',
            'shelf' => 'bi-bookshelf',
            'cold' => 'bi-snow',
            'hazmat' => 'bi-exclamation-diamond',
            default => 'bi-box',
        };
    }

    public function getUtilizationColorAttribute(): string
    {
        if ($this->utilization >= 90) return 'danger';
        if ($this->utilization >= 70) return 'warning';
        return 'success';
    }

    public function warehouse(): BelongsTo
{
    return $this->belongsTo(Warehouse::class);
}
}