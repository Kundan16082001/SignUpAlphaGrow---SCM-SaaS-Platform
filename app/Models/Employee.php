<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'tenant_id', 'user_id', 'emp_code', 'name', 'doj',
        'department_id', 'designation', 'uan', 'esi_no',
        'basic', 'hra', 'gross_salary', 'status'
    ];

    protected $casts = [
        'doj' => 'date',
        'basic' => 'decimal:2',
        'hra' => 'decimal:2',
        'gross_salary' => 'decimal:2',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function leaves(): HasMany { return $this->hasMany(Leave::class); }
    public function leaveBalances(): HasMany { return $this->hasMany(LeaveBalance::class); }
    public function payrollDetails(): HasMany { return $this->hasMany(PayrollDetail::class); }

    public function scopeActive($query) { return $query->where('status', 'active'); }

    public function getYearsOfServiceAttribute(): float
    {
        return round($this->doj->diffInYears(now()) + ($this->doj->diffInMonths(now()) % 12) / 12, 1);
    }

    public function getCurrentLeaveBalanceAttribute(): array
    {
        $balance = $this->leaveBalances()->where('year', now()->year)->first();
        if (!$balance) return ['casual' => 0, 'sick' => 0, 'privilege' => 0];

        return [
            'casual' => $balance->casual_earned - $balance->casual_used,
            'sick' => $balance->sick_earned - $balance->sick_used,
            'privilege' => $balance->privilege_earned - $balance->privilege_used,
        ];
    }
}