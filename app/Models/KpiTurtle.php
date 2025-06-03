<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class KpiTurtle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'level',
        'love_points',
        'total_love_earned',
        'experience',
        'last_fed_at',
        'last_interaction_at',
        'attributes',
        'achievements',
    ];

    protected $casts = [
        'level' => 'integer',
        'love_points' => 'integer',
        'total_love_earned' => 'integer',
        'experience' => 'integer',
        'last_fed_at' => 'datetime',
        'last_interaction_at' => 'datetime',
        'attributes' => AsCollection::class,
        'achievements' => AsCollection::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(KpiTurtleItem::class, 'kpi_user_turtle_items')
            ->withPivot('equipped', 'purchased_at')
            ->withTimestamps();
    }

    public function equippedItems()
    {
        return $this->items()->wherePivot('equipped', true);
    }

    /**
     * Calculate experience needed for the next level
     */
    public function experienceForNextLevel(): int
    {
        // Simple geometric progression: 100 * level^1.5
        return (int)(100 * pow($this->level, 1.5));
    }

    /**
     * Check if the turtle can level up
     */
    public function canLevelUp(): bool
    {
        $requiredExp = $this->experienceForNextLevel();
        return $this->experience >= $requiredExp;
    }

    /**
     * Apply level up if possible
     */
    public function processLevelUp(): bool
    {
        if (!$this->canLevelUp()) {
            return false;
        }

        $requiredExp = $this->experienceForNextLevel();
        $this->experience -= $requiredExp;
        $this->level += 1;
        $this->save();

        return true;
    }

    /**
     * Add love points to the turtle
     */
    public function addLovePoints(int $points, string $reason = 'Task completion'): void
    {
        $this->love_points += $points;
        $this->total_love_earned += $points;
        $this->last_interaction_at = now();
        $this->save();
    }

    /**
     * Add experience points to the turtle
     */
    public function addExperience(int $points): void
    {
        $this->experience += $points;
        $this->save();

        // Process level up if possible
        while ($this->canLevelUp()) {
            $this->processLevelUp();
        }
    }

    /**
     * Feed the turtle (interaction that consumes love points)
     */
    public function feed(int $lovePoints = 5): bool
    {
        if ($this->love_points < $lovePoints) {
            return false;
        }

        $this->love_points -= $lovePoints;
        $this->last_fed_at = now();
        $this->last_interaction_at = now();
        $this->addExperience($lovePoints);
        $this->save();

        return true;
    }

    /**
     * Get turtle happiness level (0-100)
     */
    public function getHappinessLevel(): int
    {
        // Base happiness from last interaction
        $lastInteractionDays = $this->last_interaction_at
            ? now()->diffInDays($this->last_interaction_at)
            : 7;

        $lastFedDays = $this->last_fed_at
            ? now()->diffInDays($this->last_fed_at)
            : 7;

        // Calculate base happiness (0-100)
        $baseHappiness = 100;

        // Reduce happiness for days without interaction
        $baseHappiness -= min(70, $lastInteractionDays * 10);

        // Reduce happiness for days without feeding
        $baseHappiness -= min(50, $lastFedDays * 7);

        // Add happiness for level
        $baseHappiness += min(20, $this->level * 2);

        // Ensure happiness is between 0-100
        return max(0, min(100, $baseHappiness));
    }

    /**
     * Get turtle mood text based on happiness level
     */
    public function getMood(): string
    {
        $happiness = $this->getHappinessLevel();

        if ($happiness >= 90) return 'Ecstatic';
        if ($happiness >= 75) return 'Happy';
        if ($happiness >= 50) return 'Content';
        if ($happiness >= 25) return 'Unhappy';
        return 'Miserable';
    }

    /**
     * Award an achievement to the turtle
     */
    public function awardAchievement(string $achievementKey, $metadata = null): void
    {
        $achievements = $this->achievements ?? collect([]);

        if (!$achievements->has($achievementKey)) {
            $achievements->put($achievementKey, [
                'awarded_at' => now()->toDateTimeString(),
                'metadata' => $metadata
            ]);

            $this->achievements = $achievements;
            $this->save();
        }
    }
}











