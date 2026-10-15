<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dealer extends Model
{
    protected $fillable = [
        'tenant_id', 'dealer_code', 'name', 'region',
        'total_orders', 'total_business', 'credit_limit',
        'outstanding', 'status'
    ];

    protected $casts = [
        'total_orders' => 'integer',
        'total_business' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'outstanding' => 'decimal:2',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    public function scopeActive($query) { return $query->where('status', 'active'); }
    public function scopeTopPerformers($query) { return $query->orderByDesc('total_business'); }

    public function getAvailableCreditAttribute(): float
    {
        return max(0, $this->credit_limit - $this->outstanding);
    }

    public function getCreditUtilizationAttribute(): float
    {
        if ($this->credit_limit <= 0) return 0;
        return round(($this->outstanding / $this->credit_limit) * 100, 1);
    }

    public function getPerformanceTierAttribute(): string
    {
        if ($this->total_business >= 1000000) return 'Platinum';
        if ($this->total_business >= 500000) return 'Gold';
        if ($this->total_business >= 100000) return 'Silver';
        return 'Bronze';
    }

    public function getTierColorAttribute(): string
    {
        return match($this->performance_tier) {
            'Platinum' => 'dark',
            'Gold' => 'warning',
            'Silver' => 'secondary',
            'Bronze' => 'danger',
            default => 'secondary',
        };
    }
}