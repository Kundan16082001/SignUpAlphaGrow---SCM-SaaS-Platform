<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    protected $fillable = [
        'return_id', 'product_id', 'batch_id', 'qty', 'accepted_qty',
        'rejected_qty', 'unit_price', 'refund_amount', 'condition'
    ];

    protected $casts = [
        'qty' => 'integer',
        'accepted_qty' => 'integer',
        'rejected_qty' => 'integer',
        'unit_price' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    // 🔑 Updated to reference ReturnOrder
    public function returnOrder(): BelongsTo
    {
        return $this->belongsTo(ReturnOrder::class, 'return_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function getConditionColorAttribute(): string
    {
        return match($this->condition) {
            'good' => 'success',
            'damaged' => 'warning',
            'defective' => 'danger',
            'expired' => 'secondary',
            default => 'primary',
        };
    }
}