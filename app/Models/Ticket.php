<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $fillable = [
        'tenant_id', 'ticket_no', 'customer_name', 'subject',
        'priority', 'status', 'assigned_to', 'description', 'resolution'
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function assignedUser(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }

    public function scopeOpen($query) { return $query->whereIn('status', ['open', 'in_progress']); }
    public function scopeResolved($query) { return $query->whereIn('status', ['resolved', 'closed']); }
    public function scopeUrgent($query) { return $query->where('priority', 'urgent'); }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'secondary',
            'medium' => 'info',
            'high' => 'warning',
            'urgent' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'danger',
            'in_progress' => 'warning',
            'resolved' => 'success',
            'closed' => 'secondary',
            default => 'secondary',
        };
    }

    public function getResolutionTimeAttribute(): ?int
    {
        if (!in_array($this->status, ['resolved', 'closed'])) return null;
        return $this->created_at->diffInHours($this->updated_at);
    }
}