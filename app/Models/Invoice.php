<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['tenant_id', 'invoice_no', 'order_id', 'customer_id', 'type', 'cgst', 'sgst', 'igst', 'total', 'status', 'due_date'];
    protected $casts = ['cgst' => 'decimal:2', 'sgst' => 'decimal:2', 'igst' => 'decimal:2', 'total' => 'decimal:2', 'due_date' => 'date'];
    
    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function order() { return $this->belongsTo(Order::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function items() { return $this->hasMany(InvoiceItem::class); }
    public function scopeOverdue($query) { return $query->where('status', 'sent')->where('due_date', '<', now()); }
    public function scopePaid($query) { return $query->where('status', 'paid'); }
}