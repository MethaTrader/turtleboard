<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action_type',
        'entity_type',
        'entity_id',
        'description',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Action type constants
     */
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';

    /**
     * Entity type constants
     */
    public const ENTITY_USER = 'user';
    public const ENTITY_EMAIL_ACCOUNT = 'email_account';
    public const ENTITY_PROXY = 'proxy';
    public const ENTITY_MEXC_ACCOUNT = 'mexc_account';
    public const ENTITY_WEB3_WALLET = 'web3_wallet';
    public const ENTITY_BALANCE = 'balance';
    public const ENTITY_KPI_GOAL = 'kpi_goal';

    /**
     * Icon mapping for different entity types and actions
     */
    public const ICONS = [
        self::ENTITY_USER => 'fas fa-user-plus',
        self::ENTITY_EMAIL_ACCOUNT => 'fas fa-envelope',
        self::ENTITY_PROXY => 'fas fa-server',
        self::ENTITY_MEXC_ACCOUNT => 'fas fa-wallet',
        self::ENTITY_WEB3_WALLET => 'fas fa-link',
        self::ENTITY_BALANCE => 'fas fa-coins',
        self::ENTITY_KPI_GOAL => 'fas fa-target',
    ];

    /**
     * Color mapping for different entity types
     */
    public const COLORS = [
        self::ENTITY_USER => 'bg-blue-100 text-blue-500',
        self::ENTITY_EMAIL_ACCOUNT => 'bg-purple-100 text-purple-500',
        self::ENTITY_PROXY => 'bg-orange-100 text-orange-500',
        self::ENTITY_MEXC_ACCOUNT => 'bg-secondary/10 text-secondary',
        self::ENTITY_WEB3_WALLET => 'bg-amber-100 text-amber-800',
        self::ENTITY_BALANCE => 'bg-green-100 text-green-500',
        self::ENTITY_KPI_GOAL => 'bg-indigo-100 text-indigo-500',
    ];

    /**
     * Get the user that performed this activity.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related entity (polymorphic relationship).
     *
     * @return MorphTo
     */
    public function entity(): MorphTo
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }

    /**
     * Get the icon for this activity.
     *
     * @return string
     */
    public function getIcon(): string
    {
        return self::ICONS[$this->entity_type] ?? 'fas fa-circle';
    }

    /**
     * Get the color classes for this activity.
     *
     * @return string
     */
    public function getColorClasses(): string
    {
        return self::COLORS[$this->entity_type] ?? 'bg-gray-100 text-gray-500';
    }

    /**
     * Get formatted time ago string.
     *
     * @return string
     */
    public function getFormattedTime(): string
    {
        $now = Carbon::now();
        $activityTime = $this->created_at;

        // Less than 1 minute ago
        if ($activityTime->diffInSeconds($now) < 60) {
            return 'Just now';
        }

        // Less than 1 hour ago
        if ($activityTime->diffInMinutes($now) < 60) {
            $minutes = (int) $activityTime->diffInMinutes($now);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        }

        // Less than 24 hours ago
        if ($activityTime->diffInHours($now) < 24) {
            $hours = (int) $activityTime->diffInHours($now);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        }

        // Yesterday
        if ($activityTime->isYesterday()) {
            return 'Yesterday at ' . $activityTime->format('g:i A');
        }

        // Less than a week ago
        if ($activityTime->diffInDays($now) < 7) {
            return $activityTime->format('l \a\t g:i A'); // Monday at 2:30 PM
        }

        // This year
        if ($activityTime->year === $now->year) {
            return $activityTime->format('M j \a\t g:i A'); // May 22 at 2:30 PM
        }

        // Different year
        return $activityTime->format('M j, Y \a\t g:i A'); // May 22, 2023 at 2:30 PM
    }

    /**
     * Get the action verb in past tense.
     *
     * @return string
     */
    public function getActionVerb(): string
    {
        return match($this->action_type) {
            self::ACTION_CREATE => 'Created',
            self::ACTION_UPDATE => 'Updated',
            self::ACTION_DELETE => 'Deleted',
            default => 'Modified',
        };
    }

    /**
     * Get the entity name in a readable format.
     *
     * @return string
     */
    public function getEntityName(): string
    {
        return match($this->entity_type) {
            self::ENTITY_USER => 'Account',
            self::ENTITY_EMAIL_ACCOUNT => 'Email Account',
            self::ENTITY_PROXY => 'Proxy',
            self::ENTITY_MEXC_ACCOUNT => 'MEXC Account',
            self::ENTITY_WEB3_WALLET => 'Web3 Wallet',
            self::ENTITY_BALANCE => 'Balance',
            self::ENTITY_KPI_GOAL => 'KPI Goal',
            default => 'Item',
        };
    }

    /**
     * Scope a query to only include recent activities.
     *
     * @param  Builder  $query
     * @param  int  $limit
     * @return Builder
     */
    public function scopeRecent($query, int $limit = 5)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope a query to filter by entity type.
     *
     * @param  Builder  $query
     * @param  string  $entityType
     * @return Builder
     */
    public function scopeOfType($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * Scope a query to filter by action type.
     *
     * @param  Builder  $query
     * @param  string  $actionType
     * @return Builder
     */
    public function scopeAction($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope a query to filter by user.
     *
     * @param  Builder  $query
     * @param  int  $userId
     * @return Builder
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}