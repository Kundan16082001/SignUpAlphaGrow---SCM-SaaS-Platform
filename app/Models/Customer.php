<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_code',
        'name',
        'gstin',
        'phone',
        'email',
        'credit_limit',
        'outstanding_amount',
        'status',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWithOutstanding($query)
    {
        return $query->where('outstanding_amount', '>', 0);
    }

    // Accessors
    public function getAvailableCreditAttribute(): float
    {
        return max(0, $this->credit_limit - $this->outstanding_amount);
    }

    public function getCreditUtilizationPercentageAttribute(): float
    {
        if ($this->credit_limit <= 0) return 0;
        return ($this->outstanding_amount / $this->credit_limit) * 100;
    }
}