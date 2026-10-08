<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'license', 'phone', 'vehicle_type', 
        'vehicle_no', 'capacity', 'experience_years', 'rating', 'status'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'experience_years' => 'integer',
        'rating' => 'decimal:2',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function shipments() { return $this->hasMany(Shipment::class); }

    public function scopeActive($query) { return $query->where('status', 'active'); }
}