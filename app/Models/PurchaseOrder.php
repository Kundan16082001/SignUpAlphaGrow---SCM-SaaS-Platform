<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'tenant_id', 'po_no', 'vendor_id', 'po_date',
        'subtotal', 'tax', 'total', 'status'
    ];

    protected $casts = [
        'po_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }

    public function scopeDraft($query) { return $query->where('status', 'draft'); }
    public function scopeApproved($query) { return $query->where('status', 'approved'); }
    public function scopePending($query) { return $query->whereIn('status', ['draft', 'approved']); }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'approved' => 'primary',
            'received' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}