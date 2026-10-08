<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory'; // Explicit table name since it's not pluralized conventionally

    protected $fillable = [
        'tenant_id',
        'product_id',
        'batch_id',
        'location',
        'warehouse_name',
        'bin_code',
        'qty',
        'reserved_qty',
        'damaged_qty',
        'status',
    ];

    protected $casts = [
        'qty' => 'integer',
        'reserved_qty' => 'integer',
        'damaged_qty' => 'integer',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    // Scopes
    public function scopeInStock($query)
    {
        return $query->where('status', 'in_stock')
                     ->where('qty', '>', 0);
    }

    public function scopeLowStock($query, $reorderLevel = 10)
    {
        return $query->where('qty', '<=', $reorderLevel)
                     ->where('qty', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('qty', '<=', 0);
    }

    // Accessors
    public function getAvailableQtyAttribute(): int
    {
        return max(0, $this->qty - $this->reserved_qty - $this->damaged_qty);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->qty > 0 && $this->qty <= 10;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->qty <= 0;
    }
}