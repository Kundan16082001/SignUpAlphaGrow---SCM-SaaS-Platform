<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDetail extends Model
{
    protected $fillable = [
        'payroll_id', 'employee_id', 'basic', 'hra', 'allowances',
        'overtime', 'gross', 'pf', 'esi', 'tds', 'net_pay',
        'working_days', 'lop_days'
    ];

    protected $casts = [
        'basic' => 'decimal:2',
        'hra' => 'decimal:2',
        'allowances' => 'decimal:2',
        'overtime' => 'decimal:2',
        'gross' => 'decimal:2',
        'pf' => 'decimal:2',
        'esi' => 'decimal:2',
        'tds' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'working_days' => 'integer',
        'lop_days' => 'integer',
    ];

    public function payroll(): BelongsTo { return $this->belongsTo(Payroll::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }

    public function getTotalDeductionsAttribute(): float
    {
        return $this->pf + $this->esi + $this->tds;
    }

    public function getTotalEarningsAttribute(): float
    {
        return $this->basic + $this->hra + $this->allowances + $this->overtime;
    }
}