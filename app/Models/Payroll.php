<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    protected $fillable = [
        'tenant_id', 'payroll_no', 'month', 'employee_count',
        'total_gross', 'total_deductions', 'total_pf', 'total_esi',
        'total_tds', 'total_net', 'status'
    ];

    protected $casts = [
        'total_gross' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_pf' => 'decimal:2',
        'total_esi' => 'decimal:2',
        'total_tds' => 'decimal:2',
        'total_net' => 'decimal:2',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function details(): HasMany { return $this->hasMany(PayrollDetail::class); }

    public function scopeDraft($query) { return $query->where('status', 'draft'); }
    public function scopeProcessed($query) { return $query->where('status', 'processed'); }
    public function scopePaid($query) { return $query->where('status', 'paid'); }

    public function getMonthFormattedAttribute(): string
    {
        return \Carbon\Carbon::parse($this->month . '-01')->format('F Y');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'processed' => 'primary',
            'paid' => 'success',
            default => 'secondary',
        };
    }
}