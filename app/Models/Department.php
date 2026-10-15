<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['tenant_id', 'name', 'code', 'head_id', 'status'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function head(): BelongsTo { return $this->belongsTo(Employee::class, 'head_id'); }
    public function employees(): HasMany { return $this->hasMany(Employee::class); }

    public function scopeActive($query) { return $query->where('status', 'active'); }

    public function getEmployeeCountAttribute(): int
    {
        return $this->employees()->where('status', 'active')->count();
    }
}