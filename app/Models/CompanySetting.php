<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = ['tenant_id', 'company_name', 'address', 'phone', 'email', 'tax_id', 'currency', 'logo_path'];
    public function tenant() { return $this->belongsTo(Tenant::class); }
}