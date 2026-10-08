<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    protected $fillable = [
        'tenant_id', 'machine_code', 'name', 'type', 'status',
        'efficiency', 'next_maintenance', 'notes'
    ];

    protected $casts = [
        'efficiency' => 'decimal:2',
        'next_maintenance' => 'date',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function workOrders() { return $this->hasMany(WorkOrder::class); }

    public function scopeOperational($query) { return $query->where('status', 'operational'); }
    public function scopeUnderMaintenance($query) { return $query->where('status', 'maintenance'); }

    public function getIsDueForMaintenanceAttribute(): bool
    {
        return $this->next_maintenance && $this->next_maintenance->lte(now()->addDays(7));
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'operational' => 'success',
            'maintenance' => 'warning',
            'breakdown' => 'danger',
            'idle' => 'secondary',
            default => 'secondary',
        };
    }
}