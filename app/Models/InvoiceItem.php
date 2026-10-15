<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'product_id', 'description', 'hsn_code',
        'qty', 'rate', 'taxable_value', 'gst_rate',
        'cgst', 'sgst', 'igst', 'total'
    ];

    protected $casts = [
        'qty' => 'integer',
        'rate' => 'decimal:2',
        'taxable_value' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'cgst' => 'decimal:2',
        'sgst' => 'decimal:2',
        'igst' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }

    public function getTotalGstAttribute(): float
    {
        return $this->cgst + $this->sgst + $this->igst;
    }

    public function getLandedTotalAttribute(): float
    {
        return $this->taxable_value + $this->total_gst;
    }
}