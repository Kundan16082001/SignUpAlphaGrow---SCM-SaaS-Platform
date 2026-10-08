<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['tenant_id', 'user_id', 'emp_code', 'name', 'doj', 'department_id', 'designation', 'uan', 'esi_no', 'basic', 'hra', 'gross_salary', 'status'];
    protected $casts = ['doj' => 'date', 'basic' => 'decimal:2', 'hra' => 'decimal:2', 'gross_salary' => 'decimal:2'];
    
    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function leaves() { return $this->hasMany(Leave::class); }
    public function leaveBalances() { return $this->hasMany(LeaveBalance::class); }
    public function payrollDetails() { return $this->hasMany(PayrollDetail::class); }
    public function scopeActive($query) { return $query->where('status', 'active'); }
}