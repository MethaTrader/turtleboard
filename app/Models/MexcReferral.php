<?php

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
        'inviter_rewarded',
        'invitee_rewarded',
        'deposit_amount',
        'deposit_date',
        'withdrawal_date',
        'promotion_period',
        'notes',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'inviter_rewarded' => 'boolean',
        'invitee_rewarded' => 'boolean',
        'deposit_amount' => 'decimal:2',
        'deposit_date' => 'datetime',
        'withdrawal_date' => 'datetime',
    ];

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
     * Scope a query to only include referrals for the first promotion of the month.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeFirstPromotion($query)
    {
        return $query->where('promotion_period', 'LIKE', '%-01-%');
    }

    /**
     * Scope a query to only include referrals for the second promotion of the month.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeSecondPromotion($query)
    {
        return $query->where('promotion_period', 'LIKE', '%-16-%');
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
     * Get the current promotion period.
     *
     * @return string
     */
    public static function getCurrentPromotionPeriod(): string
    {
        $now = now();
        $year = $now->year;
        $month = $now->format('m');

        // First half of the month (1st to 15th)
        if ($now->day <= 15) {
            return "{$year}-{$month}-01";
        }

        // Second half of the month (16th to end)
        return "{$year}-{$month}-16";
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
     * Calculate total reward amount for a MEXC account (both as inviter and invitee).
     *
     * @param int $accountId
     * @return float
     */
    public static function getTotalRewardAmount(int $accountId): float
    {
        $asInviter = self::where('inviter_account_id', $accountId)
                ->where('inviter_rewarded', true)
                ->count() * 20; // $20 per successful invitation

        $asInvitee = self::where('invitee_account_id', $accountId)
                ->where('invitee_rewarded', true)
                ->count() * 20; // $20 per successful invitation

        return $asInviter + $asInvitee;
    }
}