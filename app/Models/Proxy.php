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
        'source',
        'proxy_ipv4_id',
        'purchase_date',
        'expiry_date',
        'protocol',
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
        'purchase_date' => 'datetime',
        'expiry_date' => 'datetime',
        'response_time' => 'integer',
        'validation_status' => 'string',
    ];

    /**
     * Get the password attribute.
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
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the email account associated with this proxy.
     */
    public function emailAccount(): HasOne
    {
        return $this->hasOne(EmailAccount::class);
    }

    /**
     * Check if this proxy is from ProxyIPV4 service.
     */
    public function isFromProxyIPV4(): bool
    {
        return $this->source === 'proxy_ipv4';
    }

    /**
     * Check if proxy is expired (for ProxyIPV4 proxies).
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    /**
     * Get days remaining until expiry.
     */
    public function getDaysRemaining(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        if ($this->expiry_date->isPast()) {
            return 0;
        }

        return now()->diffInDays($this->expiry_date);
    }

    /**
     * Check if this proxy is currently in use by any email account.
     */
    public function isInUse(): bool
    {
        return $this->emailAccount()->exists();
    }

    /**
     * Get the full proxy string (IP:PORT:USERNAME:PASSWORD).
     */
    public function getFullProxyString(): string
    {
        $parts = [$this->ip_address, $this->port];

        if ($this->username) {
            $parts[] = $this->username;
            if ($this->password) {
                $parts[] = $this->password;
            }
        }

        return implode(':', $parts);
    }

    /**
     * Mark the proxy as valid.
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
     */
    public function getFlagUrl(): ?string
    {
        if (!$this->country_code) {
            return null;
        }
        return "https://flagcdn.com/32x24/{$this->country_code}.png";
    }

    /**
     * Scope a query to only include ProxyIPV4 proxies.
     */
    public function scopeFromProxyIPV4($query)
    {
        return $query->where('source', 'proxy_ipv4');
    }

    /**
     * Scope a query to only include manually added proxies.
     */
    public function scopeManuallyAdded($query)
    {
        return $query->where('source', 'manual');
    }

    /**
     * Scope a query to only include valid proxies.
     */
    public function scopeValid($query)
    {
        return $query->where('validation_status', 'valid');
    }

    /**
     * Scope a query to only include invalid proxies.
     */
    public function scopeInvalid($query)
    {
        return $query->where('validation_status', 'invalid');
    }

    /**
     * Scope a query to only include pending proxies.
     */
    public function scopePending($query)
    {
        return $query->where('validation_status', 'pending');
    }

    /**
     * Override the boot method to handle soft delete unique constraint
     */
    protected static function boot()
    {
        parent::boot();

        // When restoring a soft deleted proxy, we need to handle unique constraint
        static::restoring(function ($proxy) {
            // Check if there's already an active proxy with same IP:Port
            $existing = static::where('ip_address', $proxy->ip_address)
                ->where('port', $proxy->port)
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                throw new \Exception('A proxy with this IP and port already exists.');
            }
        });
    }
}