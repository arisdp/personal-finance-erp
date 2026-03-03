<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HealthConfig extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'workspace_id',
        'metric_key',
        'metric_label',
        'target_value',
        'comparison',
        'unit',
        'category',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Check if an actual value meets this metric's target
     */
    public function meetsTarget(float $actualValue): bool
    {
        return match ($this->comparison) {
            'gte' => $actualValue >= $this->target_value,
            'lte' => $actualValue <= $this->target_value,
            'eq' => abs($actualValue - $this->target_value) < 0.01,
            'gt' => $actualValue > $this->target_value,
            'lt' => $actualValue < $this->target_value,
            default => false,
        };
    }

    /**
     * Get the status label: healthy, warning, danger
     */
    public function getStatus(float $actualValue): string
    {
        if ($this->meetsTarget($actualValue)) {
            return 'healthy';
        }

        // Warning zone: within 20% of target
        $threshold = $this->target_value * 0.8;
        $isWarning = match ($this->comparison) {
            'gte' => $actualValue >= $threshold,
            'lte' => $actualValue <= $this->target_value * 1.2,
            default => false,
        };

        return $isWarning ? 'warning' : 'danger';
    }
}
