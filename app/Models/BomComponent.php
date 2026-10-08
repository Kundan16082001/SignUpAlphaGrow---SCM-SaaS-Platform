<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomComponent extends Model
{
    protected $fillable = [
        'bom_id', 'component_sku', 'component_name', 'qty_required',
        'unit', 'unit_cost', 'total_cost', 'wastage_percentage'
    ];

    protected $casts = [
        'qty_required' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'wastage_percentage' => 'decimal:2',
    ];

    public function bom() { return $this->belongsTo(Bom::class); }
}