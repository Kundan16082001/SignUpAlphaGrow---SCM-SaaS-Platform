<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = ['tenant_id', 'vendor_code', 'name', 'gstin', 'contact_person', 'phone', 'bank_name', 'account_number', 'ifsc_code', 'credit_limit', 'rating', 'status'];
    protected $casts = ['credit_limit' => 'decimal:2', 'rating' => 'decimal:2'];
    
    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class); }
    public function scopeActive($query) { return $query->where('status', 'active'); }
}