<?php

namespace App\Services;

use App\Models\MexcAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MexcAccountService
{
    /**
     * Create a new MEXC account.
     *
     * @param array $data
     * @return MexcAccount
     */
    public function create(array $data): MexcAccount
    {
        // Ensure user_id is set
        $data['user_id'] = Auth::id();

        // Handle empty web3_wallet_id
        if (empty($data['web3_wallet_id'])) {
            $data['web3_wallet_id'] = null;
        }

        // Log the data being inserted for debugging
        Log::info('Creating MEXC account with data:', array_merge(
            array_filter($data, fn($key) => $key !== 'password', ARRAY_FILTER_USE_KEY),
            ['password' => '[REDACTED]']
        ));

        // Create the MEXC account
        $mexcAccount = MexcAccount::create($data);

        return $mexcAccount;
    }

    /**
     * Update an existing MEXC account.
     *
     * @param MexcAccount $mexcAccount
     * @param array $data
     * @return MexcAccount
     */
    public function update(MexcAccount $mexcAccount, array $data): MexcAccount
    {
        // Handle password separately if it's not being updated
        if (!isset($data['password']) || empty($data['password'])) {
            unset($data['password']);
        }

        // Handle empty web3_wallet_id
        if (isset($data['web3_wallet_id']) && empty($data['web3_wallet_id'])) {
            $data['web3_wallet_id'] = null;
        }

        $mexcAccount->update($data);

        return $mexcAccount;
    }

    /**
     * Delete a MEXC account.
     *
     * @param MexcAccount $mexcAccount
     * @return bool
     */
    public function delete(MexcAccount $mexcAccount): bool
    {
        return $mexcAccount->delete() ?? false;
    }
}