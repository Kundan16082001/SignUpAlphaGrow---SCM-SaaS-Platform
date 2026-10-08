<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class WorkOrder extends Model
{
    protected $fillable = [
        'tenant_id', 'wo_no', 'product_id', 'product_name', 'bom_id',
        'machine_id', 'target_qty', 'completed_qty', 'scrap_qty',
        'status', 'start_date', 'deadline', 'priority', 'notes'
    ];

    protected $casts = [
        'target_qty' => 'integer',
        'completed_qty' => 'integer',
        'scrap_qty' => 'integer',
        'priority' => 'integer',
        'start_date' => 'date',
        'deadline' => 'date',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function bom() { return $this->belongsTo(Bom::class); }
    public function machine() { return $this->belongsTo(Machine::class); }

    // Accessors
    public function getProgressPercentageAttribute(): int
    {
        if ($this->target_qty <= 0) return 0;
        return min(100, (int)(($this->completed_qty / $this->target_qty) * 100));
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->deadline) return null;
        return (int) now()->startOfDay()->diffInDays($this->deadline, false);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->deadline && $this->deadline->isPast() && $this->status !== 'completed';
    }

    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            1 => 'High',
            2 => 'Medium',
            3 => 'Low',
            default => 'Medium',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            1 => 'danger',
            2 => 'warning',
            3 => 'info',
            default => 'secondary',
        };
    }

    public function scopeOverdue($query) {
        return $query->where('deadline', '<', now())
                     ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeDueSoon($query, $days = 3) {
        return $query->whereBetween('deadline', [now(), now()->addDays($days)])
                     ->whereNotIn('status', ['completed', 'cancelled']);
    }
}