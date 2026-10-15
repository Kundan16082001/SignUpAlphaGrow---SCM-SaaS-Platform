<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'file_path', 'file_type', 'file_size', 'category', 'uploaded_by'
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }

    // Accessor: Get FontAwesome/Bootstrap icon based on file type
    public function getFileIconAttribute(): string
    {
        return match(strtolower($this->file_type)) {
            'pdf' => 'bi-file-earmark-pdf text-danger',
            'doc', 'docx' => 'bi-file-earmark-word text-primary',
            'xls', 'xlsx', 'csv' => 'bi-file-earmark-excel text-success',
            'jpg', 'jpeg', 'png', 'gif' => 'bi-file-earmark-image text-info',
            'zip', 'rar' => 'bi-file-earmark-zip text-warning',
            default => 'bi-file-earmark text-secondary',
        };
    }

    // Accessor: Format file size to human-readable format
    public function getFileSizeFormattedAttribute(): string
    {
        if ($this->file_size >= 1024) {
            return round($this->file_size / 1024, 2) . ' MB';
        }
        return $this->file_size . ' KB';
    }
}