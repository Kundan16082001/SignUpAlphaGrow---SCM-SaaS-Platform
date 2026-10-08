<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnOrder extends Model
{
    // 🔑 Map to the existing 'returns' table
    protected $table = 'returns';

    protected $fillable = [
        'tenant_id', 'return_no', 'order_id', 'origin', 'destination',
        'reason', 'qty', 'value', 'refund_amount', 'status', 'direction', 'notes'
    ];

    protected $casts = [
        'qty' => 'integer',
        'value' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    // ==================== RELATIONSHIPS ====================
    
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    // ==================== ACCESSORS ====================

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'initiated' => 'info',
            'in_transit' => 'primary',
            'received' => 'warning',
            'inspected' => 'secondary',
            'approved' => 'success',
            'rejected' => 'danger',
            'completed' => 'dark',
            default => 'secondary',
        };
    }

    public function getReasonColorAttribute(): string
    {
        return match($this->reason) {
            'Defect' => 'danger',
            'QC Fail' => 'warning',
            'Damage' => 'info',
            'Recall' => 'dark',
            'Expired' => 'secondary',
            default => 'primary',
        };
    }

    public function getDirectionLabelAttribute(): string
    {
        return match($this->direction) {
            'distributor_to_warehouse' => 'Distributor → Warehouse',
            'warehouse_to_manufacturer' => 'Warehouse → Manufacturer',
            'customer_to_distributor' => 'Customer → Distributor',
            default => $this->direction,
        };
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['completed', 'rejected']);
    }
}