<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseTransferItem extends Model
{
    protected $fillable = ['transfer_id', 'product_id', 'batch_id', 'qty'];

    public function transfer(): BelongsTo { return $this->belongsTo(WarehouseTransfer::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function batch(): BelongsTo { return $this->belongsTo(Batch::class); }
}