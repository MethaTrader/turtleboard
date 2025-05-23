<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountRelationship extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'proxy_id',
        'email_account_id',
        'mexc_account_id',
        'web3_wallet_id',
        'created_by',
    ];

    /**
     * Get the user that created this relationship.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the proxy in this relationship.
     *
     * @return BelongsTo
     */
    public function proxy(): BelongsTo
    {
        return $this->belongsTo(Proxy::class);
    }

    /**
     * Get the email account in this relationship.
     *
     * @return BelongsTo
     */
    public function emailAccount(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class);
    }

    /**
     * Get the MEXC account in this relationship.
     *
     * @return BelongsTo
     */
    public function mexcAccount(): BelongsTo
    {
        return $this->belongsTo(MexcAccount::class);
    }

    /**
     * Get the Web3 wallet in this relationship.
     *
     * @return BelongsTo
     */
    public function web3Wallet(): BelongsTo
    {
        return $this->belongsTo(Web3Wallet::class);
    }

    /**
     * Get all entities in this relationship as an array.
     *
     * @return array
     */
    public function getAllEntities(): array
    {
        return [
            'proxy' => $this->proxy,
            'email_account' => $this->emailAccount,
            'mexc_account' => $this->mexcAccount,
            'web3_wallet' => $this->web3Wallet,
        ];
    }
}