<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'category',
        'love_reward',
        'experience_reward',
        'requirements',
        'metadata',
        'active',
        'is_milestone',
    ];

    protected $casts = [
        'love_reward' => 'integer',
        'experience_reward' => 'integer',
        'requirements' => 'array',
        'metadata' => 'array',
        'active' => 'boolean',
        'is_milestone' => 'boolean',
    ];

    public function userTasks(): HasMany
    {
        return $this->hasMany(KpiUserTask::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(KpiReward::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}
