<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MexcAccount extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email_account_id',
        'password',
        'user_id',
        'web3_wallet_id',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the password attribute.
     *
     * @return Attribute
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => decrypt($value),
            set: fn ($value) => encrypt($value),
        );
    }

    /**
     * Get the email account associated with this MEXC account.
     *
     * @return BelongsTo
     */
    public function emailAccount(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class);
    }

    /**
     * Get the Web3 wallet associated with this MEXC account.
     *
     * @return BelongsTo
     */
    public function web3Wallet(): BelongsTo
    {
        return $this->belongsTo(Web3Wallet::class);
    }

    /**
     * Get the user that created this MEXC account.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the referrals where this account is the inviter.
     *
     * @return HasMany
     */
    public function sentInvitations(): HasMany
    {
        return $this->hasMany(MexcReferral::class, 'inviter_account_id');
    }

    /**
     * Get the referral where this account was invited (if any).
     *
     * @return BelongsTo
     */
    public function receivedInvitation(): BelongsTo
    {
        return $this->belongsTo(MexcReferral::class, 'id', 'invitee_account_id');
    }

    /**
     * Scope a query to only include active MEXC accounts.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if the MEXC account is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the proxy associated with this MEXC account through the email account.
     *
     * @return Proxy|null
     */
    public function getProxy()
    {
        return $this->emailAccount?->proxy;
    }

    /**
     * Get the complete chain of relationships for this MEXC account.
     *
     * @return array
     */
    public function getRelationshipChain(): array
    {
        return [
            'mexc_account' => $this,
            'email_account' => $this->emailAccount,
            'proxy' => $this->emailAccount?->proxy,
            'web3_wallet' => $this->web3Wallet,
        ];
    }

    /**
     * Check if this account can invite more accounts (limit is 5).
     *
     * @return bool
     */
    public function canInviteMore(): bool
    {
        return $this->sentInvitations()->count() < 5;
    }

    /**
     * Get the remaining invitation slots for this account.
     *
     * @return int
     */
    public function getRemainingInvitationSlots(): int
    {
        return 5 - $this->sentInvitations()->count();
    }

    /**
     * Get the total rewards earned by this account.
     *
     * @return float
     */
    public function getTotalRewards(): float
    {
        return MexcReferral::getTotalRewardAmount($this->id);
    }

    /**
     * Check if this account has already been invited.
     *
     * @return bool
     */
    public function isAlreadyInvited(): bool
    {
        return MexcReferral::where('invitee_account_id', $this->id)->exists();
    }
}