<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bom extends Model
{
    protected $fillable = [
        'tenant_id', 'bom_no', 'product_id', 'product_name', 'product_sku',
        'unit_cost', 'components_count', 'status', 'version'
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'components_count' => 'integer',
        'version' => 'integer',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function components() { return $this->hasMany(BomComponent::class); }
    public function workOrders() { return $this->hasMany(WorkOrder::class); }

    public function scopeActive($query) { return $query->where('status', 'active'); }

    // ✅ ADD THIS ACCESSOR
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'warning',
            'active' => 'success',
            'archived' => 'secondary',
            default => 'secondary',
        };
    }
}