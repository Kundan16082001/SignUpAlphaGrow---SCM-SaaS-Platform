<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = [
        'tenant_id', 'vendor_code', 'name', 'gstin', 'contact_person',
        'phone', 'bank_name', 'account_number', 'ifsc_code',
        'credit_limit', 'rating', 'status'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'rating' => 'decimal:2',
    ];

    // Relationships
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function purchaseOrders(): HasMany { return $this->hasMany(PurchaseOrder::class); }
    public function expenses(): HasMany { return $this->hasMany(Expense::class, 'vendor', 'name'); }

    // Scopes
    public function scopeActive($query) { return $query->where('status', 'active'); }
    public function scopeTopRated($query) { return $query->orderByDesc('rating'); }

    // Accessors
    public function getRatingStarsAttribute(): string
    {
        return str_repeat('★', round($this->rating)) . str_repeat('☆', 5 - round($this->rating));
    }

    public function getRatingColorAttribute(): string
    {
        if ($this->rating >= 4.5) return 'success';
        if ($this->rating >= 3.5) return 'warning';
        return 'danger';
    }
}