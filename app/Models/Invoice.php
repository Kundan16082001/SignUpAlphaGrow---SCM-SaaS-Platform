<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'tenant_id', 'invoice_no', 'order_id', 'customer_id',
        'type', 'cgst', 'sgst', 'igst', 'total', 'status', 'due_date'
    ];

    protected $casts = [
        'cgst' => 'decimal:2',
        'sgst' => 'decimal:2',
        'igst' => 'decimal:2',
        'total' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class); }

    public function scopeDraft($query) { return $query->where('status', 'draft'); }
    public function scopeSent($query) { return $query->where('status', 'sent'); }
    public function scopePaid($query) { return $query->where('status', 'paid'); }
    public function scopeOverdue($query) { return $query->where('status', 'sent')->where('due_date', '<', now()); }

    public function getTotalGstAttribute(): float
    {
        return $this->cgst + $this->sgst + $this->igst;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'sent' && $this->due_date && $this->due_date->isPast();
    }

    public function getDaysOverdueAttribute(): ?int
    {
        if (!$this->is_overdue) return null;
        return (int) now()->diffInDays($this->due_date);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'sent' => 'warning',
            'paid' => 'success',
            'overdue' => 'danger',
            'cancelled' => 'dark',
            default => 'secondary',
        };
    }
}