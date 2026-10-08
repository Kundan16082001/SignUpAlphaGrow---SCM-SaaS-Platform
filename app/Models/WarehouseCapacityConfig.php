<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseCapacityConfig extends Model
{
    protected $table = 'warehouse_capacity_config';

    protected $fillable = [
        'tenant_id', 'warehouse_name', 'total_area_sqft',
        'pallet_capacity', 'shelf_capacity', 'cold_capacity',
        'hazmat_capacity', 'max_weight_tons'
    ];

    protected $casts = [
        'total_area_sqft' => 'decimal:2',
        'pallet_capacity' => 'integer',
        'shelf_capacity' => 'integer',
        'cold_capacity' => 'integer',
        'hazmat_capacity' => 'integer',
        'max_weight_tons' => 'decimal:2',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }

    public function getTotalCapacityAttribute(): int
    {
        return $this->pallet_capacity + $this->shelf_capacity + $this->cold_capacity + $this->hazmat_capacity;
    }
}