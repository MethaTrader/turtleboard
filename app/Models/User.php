<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is an administrator.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'administrator';
    }

    /**
     * Check if user is an account manager.
     *
     * @return bool
     */
    public function isAccountManager(): bool
    {
        return $this->role === 'account_manager';
    }

    /**
     * Scope a query to only include administrators.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeAdministrators($query)
    {
        return $query->where('role', 'administrator');
    }

    /**
     * Scope a query to only include account managers.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeAccountManagers($query)
    {
        return $query->where('role', 'account_manager');
    }

    /**
     * Get the email accounts created by the user.
     *
     * @return HasMany
     */
    public function emailAccounts(): HasMany
    {
        return $this->hasMany(EmailAccount::class);
    }

    /**
     * Get the proxies created by the user.
     *
     * @return HasMany
     */
    public function proxies(): HasMany
    {
        return $this->hasMany(Proxy::class);
    }

    /**
     * Get the MEXC accounts created by the user.
     *
     * @return HasMany
     */
    public function mexcAccounts(): HasMany
    {
        return $this->hasMany(MexcAccount::class);
    }

    /**
     * Get the activities performed by this user.
     *
     * @return HasMany
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Get the Web3 wallets created by the user.
     *
     * @return HasMany
     */
    public function web3Wallets(): HasMany
    {
        return $this->hasMany(Web3Wallet::class);
    }
}