<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'metric_type',
        'target_value',
        'love_reward',
        'experience_reward',
        'start_date',
        'end_date',
        'period_type',
        'active',
        'metadata',
    ];

    protected $casts = [
        'target_value' => 'integer',
        'love_reward' => 'integer',
        'experience_reward' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean',
        'metadata' => 'array',
    ];

    public function userTargets(): HasMany
    {
        return $this->hasMany(KpiUserTarget::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }

    public function isActive(): bool
    {
        $today = now()->toDateString();
        return $this->active &&
            $this->start_date <= $today &&
            $this->end_date >= $today;
    }
}
