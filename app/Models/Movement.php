<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    protected $fillable = ['tenant_id', 'batch_id', 'shipment_id', 'action', 'from_location', 'to_location', 'from_wing', 'to_wing', 'qty', 'actor_id', 'status'];
    
    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function batch() { return $this->belongsTo(Batch::class); }
    public function shipment() { return $this->belongsTo(Shipment::class); }
    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }
}