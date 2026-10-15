<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Shipment extends Model
{
    protected $fillable = [
        'tenant_id', 'shipment_no', 'direction', 'origin', 'destination',
        'carrier_id', 'driver_id', 'vehicle_id', 'freight_cost', 
        'eta_date', 'status', 'notes'
    ];

    protected $casts = [
        'freight_cost' => 'decimal:2',
        'eta_date' => 'date',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function carrier() { return $this->belongsTo(Carrier::class); }
    public function driver() { return $this->belongsTo(Driver::class); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }

    public function getDaysUntilEtaAttribute(): ?int
    {
        if (!$this->eta_date) return null;
        return (int) now()->startOfDay()->diffInDays($this->eta_date, false);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
    public function getIsOverdueAttribute(): bool
    {
        return $this->eta_date && $this->eta_date->isPast() && $this->status !== 'delivered';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'secondary',
            'in_transit' => 'primary',
            'delivered' => 'success',
            'delayed' => 'danger',
            'cancelled' => 'dark',
            default => 'secondary',
        };
    }

    public function getDirectionIconAttribute(): string
    {
        return match($this->direction) {
            'inbound' => 'bi-box-arrow-in-down',
            'outbound' => 'bi-box-arrow-up',
            'return' => 'bi-arrow-counterclockwise',
            default => 'bi-truck',
        };
    }

    public function scopeInTransit($query) { return $query->where('status', 'in_transit'); }
    public function scopeOverdue($query) { 
        return $query->where('eta_date', '<', now())
                     ->whereNotIn('status', ['delivered', 'cancelled']); 
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}