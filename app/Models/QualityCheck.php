<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityCheck extends Model
{
    protected $fillable = [
        'tenant_id',
        'qc_no',
        'batch_id',
        'inspector_name',
        'sample_size',
        'defect_count',
        'qc_score',
        'result',
        'parameters',
    ];

    protected $casts = [
        'sample_size' => 'integer',
        'defect_count' => 'integer',
        'qc_score' => 'decimal:2',
    ];

    // ==================== RELATIONSHIPS ====================
    
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    // ==================== SCOPES ====================
    
    public function scopePassed($query)
    {
        return $query->where('result', 'pass');
    }

    public function scopeFailed($query)
    {
        return $query->where('result', 'fail');
    }

    public function scopePending($query)
    {
        return $query->where('result', 'pending');
    }

    public function scopeRework($query)
    {
        return $query->where('result', 'rework');
    }

    public function scopeByInspector($query, $inspectorName)
    {
        return $query->where('inspector_name', $inspectorName);
    }

    public function scopeHighScore($query, $minScore = 90)
    {
        return $query->where('qc_score', '>=', $minScore);
    }

    public function scopeLowScore($query, $maxScore = 70)
    {
        return $query->where('qc_score', '<=', $maxScore);
    }

    // ==================== ACCESSORS ====================
    
    /**
     * Get the defect percentage
     */
    public function getDefectPercentageAttribute(): float
    {
        if ($this->sample_size <= 0) return 0;
        return round(($this->defect_count / $this->sample_size) * 100, 2);
    }

    /**
     * Get the pass rate percentage (inverse of defect rate)
     */
    public function getPassRateAttribute(): float
    {
        return round(100 - $this->defect_percentage, 2);
    }

    /**
     * Get result color for UI badges
     */
    public function getResultColorAttribute(): string
    {
        return match($this->result) {
            'pass' => 'success',
            'fail' => 'danger',
            'rework' => 'warning',
            'pending' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get result icon for UI
     */
    public function getResultIconAttribute(): string
    {
        return match($this->result) {
            'pass' => 'bi-check-circle-fill',
            'fail' => 'bi-x-circle-fill',
            'rework' => 'bi-arrow-repeat',
            'pending' => 'bi-hourglass-split',
            default => 'bi-question-circle',
        };
    }

    /**
     * Get result label (human-readable)
     */
    public function getResultLabelAttribute(): string
    {
        return match($this->result) {
            'pass' => 'Passed',
            'fail' => 'Failed',
            'rework' => 'Needs Rework',
            'pending' => 'Pending Review',
            default => ucfirst($this->result ?? 'Unknown'),
        };
    }

    /**
     * Get QC score color based on thresholds
     */
    public function getScoreColorAttribute(): string
    {
        if ($this->qc_score >= 90) return 'success';
        if ($this->qc_score >= 70) return 'warning';
        return 'danger';
    }

    /**
     * Check if the batch passed QC
     */
    public function getIsPassedAttribute(): bool
    {
        return $this->result === 'pass';
    }

    /**
     * Check if the batch failed QC
     */
    public function getIsFailedAttribute(): bool
    {
        return $this->result === 'fail';
    }

    /**
     * Get formatted parameters (if stored as JSON string)
     */
    public function getFormattedParametersAttribute(): array
    {
        if (empty($this->parameters)) return [];
        
        // If it's a JSON string, decode it
        if (is_string($this->parameters)) {
            $decoded = json_decode($this->parameters, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            // Otherwise, split by comma
            return array_map('trim', explode(',', $this->parameters));
        }
        
        return (array) $this->parameters;
    }

    /**
     * Get a summary string for display
     */
    public function getSummaryAttribute(): string
    {
        return sprintf(
            'QC %s | Score: %.1f%% | Defects: %d/%d (%.1f%%)',
            $this->qc_no,
            $this->qc_score,
            $this->defect_count,
            $this->sample_size,
            $this->defect_percentage
        );
    }
}