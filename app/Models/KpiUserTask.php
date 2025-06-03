<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiUserTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kpi_task_id',
        'progress',
        'target',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'progress' => 'integer',
        'target' => 'integer',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(KpiTask::class, 'kpi_task_id');
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isInProgress(): bool
    {
        return $this->progress > 0 && $this->progress < $this->target;
    }

    public function getCompletionPercentage(): int
    {
        if ($this->target <= 0) {
            return 0;
        }

        return min(100, (int)(($this->progress / $this->target) * 100));
    }

    /**
     * Increment progress toward task completion
     */
    public function incrementProgress(int $amount = 1): bool
    {
        // Don't increment if already completed
        if ($this->isCompleted()) {
            return false;
        }

        $this->progress += $amount;

        // Check if task is now completed
        if ($this->progress >= $this->target) {
            $this->progress = $this->target; // Cap at target
            $this->completed_at = now();
        }

        $this->save();

        return $this->isCompleted();
    }
}
