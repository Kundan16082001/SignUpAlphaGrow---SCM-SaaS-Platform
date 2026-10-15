<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'tenant_id', 'expense_no', 'category_id', 'vendor', 'amount',
        'gst', 'tds', 'total', 'payment_mode', 'status',
        'bill_attachment', 'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gst' => 'decimal:2',
        'tds' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function category(): BelongsTo { return $this->belongsTo(ExpenseCategory::class, 'category_id'); }

    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeApproved($query) { return $query->where('status', 'approved'); }
    public function scopePaid($query) { return $query->where('status', 'paid'); }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'paid' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function getNetPayableAttribute(): float
    {
        return $this->total - $this->tds;
    }
}