<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Syncable; 

class Product extends Model
{
    use Syncable;
    protected $fillable = [
        'tenant_id', 'sku', 'name', 'category', 'unit', 'gst_rate', 'hsn_code', 
        'price', 'cost_price', 'stock_qty', 'reorder_level', 'status','is_synced', // ✅ 3. Make sure this is fillable
        'last_synced_at',
    ];

    // Accessor to calculate stock status dynamically
    public function getStockStatusAttribute()
    {
        if ($this->stock_qty <= 0) return 'out_of_stock';
        if ($this->stock_qty <= $this->reorder_level) return 'low';
        return 'ok';
    }

    public function batches() { return $this->hasMany(Batch::class); }
    public function inventory() { return $this->hasMany(Inventory::class); }
}