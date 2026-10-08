<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GrqrPosting extends Model
{
    protected $fillable = ['tenant_id', 'posting_no', 'type', 'shipment_id', 'batch_id', 'expected_qty', 'received_qty', 'damaged_qty', 'qc_result', 'qc_score', 'status'];
    
    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function batch() { return $this->belongsTo(Batch::class); }
    public function shipment() { return $this->belongsTo(Shipment::class); }
}