<?php
// app/Models/MexcReferral.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MexcReferral extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inviter_account_id',
        'invitee_account_id',
        'status',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status constants for referrals
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the inviter MEXC account.
     *
     * @return BelongsTo
     */
    public function inviterAccount(): BelongsTo
    {
        return $this->belongsTo(MexcAccount::class, 'inviter_account_id');
    }

    /**
     * Get the invitee MEXC account.
     *
     * @return BelongsTo
     */
    public function inviteeAccount(): BelongsTo
    {
        return $this->belongsTo(MexcAccount::class, 'invitee_account_id');
    }

    /**
     * Get the user who created this referral.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include referrals by status.
     *
     * @param  Builder  $query
     * @param  string  $status
     * @return Builder
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include referrals for a specific inviter.
     *
     * @param  Builder  $query
     * @param  int  $inviterId
     * @return Builder
     */
    public function scopeByInviter($query, $inviterId)
    {
        return $query->where('inviter_account_id', $inviterId);
    }

    /**
     * Scope a query to only include referrals for a specific invitee.
     *
     * @param  Builder  $query
     * @param  int  $inviteeId
     * @return Builder
     */
    public function scopeByInvitee($query, $inviteeId)
    {
        return $query->where('invitee_account_id', $inviteeId);
    }

    /**
     * Check if an account has reached the maximum number of invitations (5).
     *
     * @param int $accountId
     * @return bool
     */
    public static function hasReachedInviteLimit(int $accountId): bool
    {
        $count = self::where('inviter_account_id', $accountId)->count();
        return $count >= 5;
    }

    /**
     * Check if an account is already invited by someone.
     *
     * @param int $accountId
     * @return bool
     */
    public static function isAlreadyInvited(int $accountId): bool
    {
        return self::where('invitee_account_id', $accountId)->exists();
    }

    /**
     * Get the color for this referral based on status.
     *
     * @return string
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => '#00DEA3',
            self::STATUS_PENDING => '#F59E0B',
            self::STATUS_CANCELLED => '#F56565',
            default => '#6B7280',
        };
    }

    /**
     * Get the status label in a human-readable format.
     *
     * @return string
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Mark the referral as completed.
     *
     * @return bool
     */
    public function markAsCompleted(): bool
    {
        return $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Mark the referral as cancelled.
     *
     * @return bool
     */
    public function markAsCancelled(): bool
    {
        return $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Get statistics for all referrals.
     *
     * @return array
     */
    public static function getStatistics(): array
    {
        $total = self::count();
        $pending = self::where('status', self::STATUS_PENDING)->count();
        $completed = self::where('status', self::STATUS_COMPLETED)->count();
        $cancelled = self::where('status', self::STATUS_CANCELLED)->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Boot the model to set default values.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($referral) {
            if (empty($referral->status)) {
                $referral->status = self::STATUS_PENDING;
            }
        });
    }
}