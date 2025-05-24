<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Web3Wallet extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'address',
        'seed_phrase',
        'network',
        'user_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'seed_phrase',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'network' => 'string',
    ];

    /**
     * Get the seed phrase attribute.
     *
     * @return Attribute
     */
    protected function seedPhrase(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => decrypt($value),
            set: fn ($value) => encrypt($value),
        );
    }

    /**
     * Get the user that created this Web3 wallet.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the MEXC account associated with this Web3 wallet.
     *
     * @return HasOne
     */
    public function mexcAccount(): HasOne
    {
        return $this->hasOne(MexcAccount::class);
    }

    /**
     * Get a formatted version of the wallet address with ellipsis in the middle.
     *
     * @return string
     */
    public function getFormattedAddress(): string
    {
        if (strlen($this->address) <= 10) {
            return $this->address;
        }

        return substr($this->address, 0, 6) . '...' . substr($this->address, -4);
    }

    /**
     * Scope a query to search by address.
     *
     * @param  Builder  $query
     * @param  string  $search
     * @return Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('address', 'like', "%{$search}%");
    }

    /**
     * Scope a query to filter by network.
     *
     * @param  Builder  $query
     * @param  string  $network
     * @return Builder
     */
    public function scopeNetwork($query, $network)
    {
        return $query->where('network', $network);
    }

    /**
     * Check if the wallet is connected to a MEXC account.
     *
     * @return bool
     */
    public function isConnectedToMexc(): bool
    {
        return $this->mexcAccount()->exists();
    }

    /**
     * Get the blockchain explorer URL for this wallet address.
     *
     * @return string|null
     */
    public function getExplorerUrl(): ?string
    {
        switch ($this->network) {
            case 'ethereum':
                return 'https://etherscan.io/address/' . $this->address;
            case 'binance':
                return 'https://bscscan.com/address/' . $this->address;
            default:
                return null;
        }
    }
}