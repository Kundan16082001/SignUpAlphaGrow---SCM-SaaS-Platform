<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosTransaction extends Model
{
    protected $fillable = [
        'tenant_id', 'receipt_no', 'user_id',
        'subtotal', 'tax', 'total',
        'payment_method', 'status', 'items',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'items' => 'array', // JSON column
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function scopeToday($query) { return $query->whereDate('created_at', today()); }
    public function scopeCompleted($query) { return $query->where('status', 'completed'); }
    public function scopeRefunded($query) { return $query->where('status', 'refunded'); }

    public function getPaymentMethodIconAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'bi-cash-coin',
            'upi' => 'bi-phone',
            'card' => 'bi-credit-card',
            'net_banking' => 'bi-bank',
            default => 'bi-wallet2',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'completed' => 'success',
            'refunded' => 'warning',
            'void' => 'danger',
            default => 'secondary',
        };
    }

    public function getItemCountAttribute(): int
    {
        return is_array($this->items) ? count($this->items) : 0;
    }
}