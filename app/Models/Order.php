<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'tenant_id', 'customer_id', 'order_no', 'type', 'subtotal', 'tax', 
        'total', 'payment_status', 'status', 'order_date'
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
}