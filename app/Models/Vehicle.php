<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'tenant_id', 'reg_no', 'type', 'model', 'capacity', 
        'fuel_type', 'year', 'insurance_expiry', 'fitness_expiry', 'status'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'year' => 'integer',
        'insurance_expiry' => 'date',
        'fitness_expiry' => 'date',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function shipments() { return $this->hasMany(Shipment::class); }

    public function scopeAvailable($query) { return $query->where('status', 'available'); }

    public function getIsInsuranceExpiringAttribute(): bool
    {
        return $this->insurance_expiry && $this->insurance_expiry->lte(now()->addDays(30));
    }
}