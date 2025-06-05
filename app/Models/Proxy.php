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
        'metadata', // Add metadata field for ProxyIPV4 data
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
        'metadata' => 'array', // Cast metadata as JSON
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

        return "https://flagcdn.com/32x24/{$this->country_code}.png";
    }

    /**
     * Check if this proxy is from ProxyIPV4 service.
     *
     * @return bool
     */
    public function isFromProxyIPV4(): bool
    {
        return isset($this->metadata['source']) && $this->metadata['source'] === 'proxy_ipv4';
    }

    /**
     * Get ProxyIPV4 specific data.
     *
     * @return array|null
     */
    public function getProxyIPV4Data(): ?array
    {
        if (!$this->isFromProxyIPV4()) {
            return null;
        }

        return [
            'proxy_id' => $this->metadata['proxy_id'] ?? null,
            'purchase_date' => $this->metadata['purchase_date'] ?? null,
            'expiry_date' => $this->metadata['expiry_date'] ?? null,
            'protocol' => $this->metadata['protocol'] ?? 'http',
        ];
    }

    /**
     * Get expiry date for ProxyIPV4 proxies.
     *
     * @return \Carbon\Carbon|null
     */
    public function getExpiryDate(): ?\Carbon\Carbon
    {
        $data = $this->getProxyIPV4Data();
        if ($data && $data['expiry_date']) {
            return \Carbon\Carbon::parse($data['expiry_date']);
        }
        return null;
    }

    /**
     * Get purchase date for ProxyIPV4 proxies.
     *
     * @return \Carbon\Carbon|null
     */
    public function getPurchaseDate(): ?\Carbon\Carbon
    {
        $data = $this->getProxyIPV4Data();
        if ($data && $data['purchase_date']) {
            return \Carbon\Carbon::parse($data['purchase_date']);
        }
        return null;
    }

    /**
     * Check if proxy is expired (for ProxyIPV4 proxies).
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        $expiryDate = $this->getExpiryDate();
        if (!$expiryDate) {
            return false; // No expiry date means not expired
        }
        return $expiryDate->isPast();
    }

    /**
     * Get days remaining until expiry.
     *
     * @return int|null
     */
    public function getDaysRemaining(): ?int
    {
        $expiryDate = $this->getExpiryDate();
        if (!$expiryDate) {
            return null;
        }

        if ($expiryDate->isPast()) {
            return 0;
        }

        return now()->diffInDays($expiryDate);
    }

    /**
     * Check if this proxy is currently in use by any email account.
     *
     * @return bool
     */
    public function isInUse(): bool
    {
        return $this->emailAccount()->exists();
    }

    /**
     * Get the status badge color based on proxy status.
     *
     * @return string
     */
    public function getStatusBadgeColor(): string
    {
        if ($this->isFromProxyIPV4() && $this->isExpired()) {
            return 'bg-gray-500'; // Expired
        }

        return match($this->validation_status) {
            'valid' => 'bg-success',
            'invalid' => 'bg-danger',
            'pending' => 'bg-warning',
            default => 'bg-gray-500',
        };
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
     * Scope a query to only include ProxyIPV4 proxies.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeFromProxyIPV4($query)
    {
        return $query->where('metadata->source', 'proxy_ipv4');
    }

    /**
     * Scope a query to only include manually added proxies.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeManuallyAdded($query)
    {
        return $query->where(function($q) {
            $q->whereNull('metadata')
                ->orWhere('metadata->source', '!=', 'proxy_ipv4')
                ->orWhereJsonDoesntContain('metadata', ['source' => 'proxy_ipv4']);
        });
    }

    /**
     * Scope a query to only include non-expired ProxyIPV4 proxies.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            // Include all manually added proxies
            $q->whereNull('metadata')
                ->orWhereJsonDoesntContain('metadata->source', 'proxy_ipv4')
                // Include non-expired ProxyIPV4 proxies
                ->orWhere(function($subQ) {
                    $subQ->whereJsonContains('metadata->source', 'proxy_ipv4')
                        ->where(function($dateQ) {
                            $dateQ->whereNull('metadata->expiry_date')
                                ->orWhereRaw("STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.expiry_date')), '%Y-%m-%d %H:%i:%s') > NOW()");
                        });
                });
        });
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

    /**
     * Get the ProxyIPV4 ID from metadata.
     *
     * @return string|null
     */
    public function getProxyIPV4Id(): ?string
    {
        if (!$this->metadata || !isset($this->metadata['source']) || $this->metadata['source'] !== 'proxy_ipv4') {
            return null;
        }

        return $this->metadata['proxy_id'] ?? null;
    }
}