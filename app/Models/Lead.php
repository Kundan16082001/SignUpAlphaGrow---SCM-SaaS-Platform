<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = [
        'tenant_id', 'lead_code', 'company_name', 'contact_person',
        'source', 'value', 'status', 'assigned_to', 'follow_up_date'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'follow_up_date' => 'date',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function assignedUser(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }

    public function scopeNew($query) { return $query->where('status', 'new'); }
    public function scopeHot($query) { return $query->whereIn('status', ['contacted', 'quoted']); }
    public function scopeWon($query) { return $query->where('status', 'won'); }
    public function scopeLost($query) { return $query->where('status', 'lost'); }

    public function getIsOverdueAttribute(): bool
    {
        return $this->follow_up_date && $this->follow_up_date->isPast() && !in_array($this->status, ['won', 'lost']);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'new' => 'info',
            'contacted' => 'primary',
            'quoted' => 'warning',
            'won' => 'success',
            'lost' => 'danger',
            default => 'secondary',
        };
    }

    public function getConversionRateAttribute(): string
    {
        $total = Lead::where('tenant_id', $this->tenant_id)->whereIn('status', ['won', 'lost'])->count();
        $won = Lead::where('tenant_id', $this->tenant_id)->where('status', 'won')->count();
        return $total > 0 ? round(($won / $total) * 100, 1) . '%' : '0%';
    }
}