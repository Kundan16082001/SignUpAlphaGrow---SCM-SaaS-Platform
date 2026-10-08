<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrier extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'type', 'contact', 'gstin', 
        'coverage', 'rating', 'status'
    ];

    protected $casts = [
        'rating' => 'decimal:2',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function shipments() { return $this->hasMany(Shipment::class); }

    public function scopeActive($query) { return $query->where('status', 'active'); }
}