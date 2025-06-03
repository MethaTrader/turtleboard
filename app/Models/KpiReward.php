<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kpi_task_id',
        'love_points',
        'experience_points',
        'reason',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'love_points' => 'integer',
        'experience_points' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(KpiTask::class, 'kpi_task_id');
    }

    public function source()
    {
        return $this->morphTo();
    }
}
