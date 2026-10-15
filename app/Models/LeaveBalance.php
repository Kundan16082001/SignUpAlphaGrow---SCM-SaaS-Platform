<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = [
        'employee_id', 'year',
        'casual_earned', 'casual_used',
        'sick_earned', 'sick_used',
        'privilege_earned', 'privilege_used'
    ];

    protected $casts = [
        'year' => 'integer',
        'casual_earned' => 'decimal:2',
        'casual_used' => 'decimal:2',
        'sick_earned' => 'decimal:2',
        'sick_used' => 'decimal:2',
        'privilege_earned' => 'decimal:2',
        'privilege_used' => 'decimal:2',
    ];

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }

    public function getCasualRemainingAttribute(): float { return $this->casual_earned - $this->casual_used; }
    public function getSickRemainingAttribute(): float { return $this->sick_earned - $this->sick_used; }
    public function getPrivilegeRemainingAttribute(): float { return $this->privilege_earned - $this->privilege_used; }

    public function getTotalRemainingAttribute(): float
    {
        return $this->casual_remaining + $this->sick_remaining + $this->privilege_remaining;
    }
}