<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class EmailAccount extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'provider',
        'email_address',
        'password',
        'user_id',
        'proxy_id',
        'status',
        'first_name',
        'last_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'provider' => 'string',
        'status' => 'string',
    ];

    /**
     * The available email providers.
     *
     * @var array<string>
     */
    public const PROVIDERS = ['Gmail', 'Outlook', 'Yahoo', 'Rambler'];

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
     * Get the user that created this email account.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the proxy associated with this email account.
     *
     * @return BelongsTo
     */
    public function proxy(): BelongsTo
    {
        return $this->belongsTo(Proxy::class);
    }

    /**
     * Get the MEXC account associated with this email account.
     *
     * @return HasOne
     */
    public function mexcAccount(): HasOne
    {
        return $this->hasOne(MexcAccount::class);
    }

    /**
     * Scope a query to only include active email accounts.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to filter by provider.
     *
     * @param  Builder  $query
     * @param  string  $provider
     * @return Builder
     */
    public function scopeProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Check if the email account is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the full name or email if name is not available.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        } elseif ($this->first_name) {
            return $this->first_name;
        } elseif ($this->last_name) {
            return $this->last_name;
        }

        return $this->email_address;
    }
}