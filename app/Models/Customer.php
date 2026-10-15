<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Syncable;

class Customer extends Model
{
    use HasFactory,Syncable;

    protected $fillable = [
        'tenant_id', 'customer_code', 'name', 'type', 'gstin', 'pan', 
        'phone', 'email', 'address', 'city', 'state', 'pincode', 'country', 
        'credit_limit', 'outstanding_amount', 'status',
        'is_synced',
        'last_synced_at',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }

    public function scopeActive($query) { return $query->where('status', 'active'); }
}