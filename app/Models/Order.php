<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Syncable;

class Order extends Model
{
    use Syncable;

    protected $fillable = [
        'tenant_id', 'customer_id', 'order_no', 'type', 'subtotal', 'tax', 
        'total', 'payment_status', 'status', 'order_date',
        'is_synced', // ✅ 3. Make sure this is fillable
        'last_synced_at',
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
}