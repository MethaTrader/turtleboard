<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiUserTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kpi_target_id',
        'current_value',
        'achieved',
        'achieved_at',
    ];

    protected $casts = [
        'current_value' => 'integer',
        'achieved' => 'boolean',
        'achieved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(KpiTarget::class, 'kpi_target_id');
    }

    public function getProgressPercentage(): int
    {
        if (!$this->target || $this->target->target_value <= 0) {
            return 0;
        }

        return min(100, (int)(($this->current_value / $this->target->target_value) * 100));
    }

    /**
     * Increment progress toward target
     */
    public function incrementProgress(int $amount = 1): bool
    {
        // Don't increment if already achieved
        if ($this->achieved) {
            return false;
        }

        $this->current_value += $amount;

        // Check if target is now achieved
        if ($this->target && $this->current_value >= $this->target->target_value) {
            $this->current_value = $this->target->target_value; // Cap at target
            $this->achieved = true;
            $this->achieved_at = now();
        }

        $this->save();

        return $this->achieved;
    }
}
