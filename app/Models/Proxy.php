<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Proxy extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ip_address',
        'port',
        'username',
        'password',
        'last_validation_date',
        'validation_status',
        'response_time',
        'geolocation',
        'country_code',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'port' => 'integer',
        'last_validation_date' => 'datetime',
        'response_time' => 'integer',
        'validation_status' => 'string',
    ];

    /**
     * Get the password attribute.
     *
     * @return Attribute
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    /**
     * Get the user that created this proxy.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the email account associated with this proxy.
     *
     * @return HasOne
     */
    public function emailAccount(): HasOne
    {
        return $this->hasOne(EmailAccount::class);
    }

    /**
     * Get the full proxy string (IP:PORT:USERNAME:PASSWORD).
     *
     * @return string
     */
    public function getFullProxyString(): string
    {
        $parts = [$this->ip_address, $this->port];

        if ($this->username) {
            $parts[] = $this->username;

            if ($this->password) {
                $parts[] = $this->getOriginal('password') ? $this->password : '';
            }
        }

        return implode(':', $parts);
    }

    /**
     * Check if the proxy is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->validation_status === 'valid';
    }

    /**
     * Mark the proxy as valid.
     *
     * @param  int  $responseTime
     * @param  string|null  $geolocation
     * @param  string|null  $countryCode
     * @return bool
     */
    public function markAsValid(int $responseTime, ?string $geolocation = null, ?string $countryCode = null): bool
    {
        return $this->update([
            'validation_status' => 'valid',
            'last_validation_date' => now(),
            'response_time' => $responseTime,
            'geolocation' => $geolocation,
            'country_code' => $countryCode,
        ]);
    }

    /**
     * Mark the proxy as invalid.
     *
     * @return bool
     */
    public function markAsInvalid(): bool
    {
        return $this->update([
            'validation_status' => 'invalid',
            'last_validation_date' => now(),
        ]);
    }

    /**
     * Get the flag URL for the proxy's country.
     *
     * @return string|null
     */
    public function getFlagUrl(): ?string
    {
        if (!$this->country_code) {
            return null;
        }

        return "https://flagpedia.net/data/flags/emoji/twitter/64/{$this->country_code}.png";
    }

    /**
     * Scope a query to only include valid proxies.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeValid($query)
    {
        return $query->where('validation_status', 'valid');
    }

    /**
     * Scope a query to only include invalid proxies.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeInvalid($query)
    {
        return $query->where('validation_status', 'invalid');
    }

    /**
     * Scope a query to only include pending proxies.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopePending($query)
    {
        return $query->where('validation_status', 'pending');
    }

    /**
     * Scope a query to include proxies that need validation
     * (either pending or validated more than 24 hours ago).
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeNeedsValidation($query)
    {
        return $query->where('validation_status', 'pending')
            ->orWhere(function ($query) {
                $query->where('last_validation_date', '<', now()->subDay());
            });
    }
}