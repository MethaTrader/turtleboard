<?php

namespace App\Services;

use App\Models\Web3Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Web3WalletService
{
    /**
     * Create a new Web3 wallet.
     *
     * @param array $data
     * @return Web3Wallet
     */
    public function create(array $data): Web3Wallet
    {
        // Ensure user_id is set
        $data['user_id'] = Auth::id();

        // Verify we have the required fields
        if (empty($data['address']) || empty($data['seed_phrase'])) {
            Log::error('Missing required wallet data', [
                'network' => $data['network'] ?? 'unknown',
                'method' => $data['creation_method'] ?? 'unknown',
                'has_address' => !empty($data['address']),
                'has_seed_phrase' => !empty($data['seed_phrase']),
            ]);
            throw new \InvalidArgumentException('Wallet address and seed phrase are required');
        }

        // Log wallet creation (without sensitive data)
        Log::info('Creating Web3 wallet', [
            'network' => $data['network'] ?? 'unknown',
            'method' => $data['creation_method'] ?? 'unknown',
            'user_id' => Auth::id(),
            'address_prefix' => substr($data['address'], 0, 6) . '...',
        ]);

        // Create the wallet
        $wallet = Web3Wallet::create([
            'address' => $data['address'],
            'seed_phrase' => $data['seed_phrase'],
            'user_id' => Auth::id(),
            'network' => $data['network']
        ]);

        return $wallet;
    }

    /**
     * Update an existing Web3 wallet.
     *
     * @param Web3Wallet $wallet
     * @param array $data
     * @return Web3Wallet
     */
    public function update(Web3Wallet $wallet, array $data): Web3Wallet
    {
        // Handle seed phrase separately if it's not being updated
        if (!isset($data['seed_phrase']) || empty($data['seed_phrase'])) {
            unset($data['seed_phrase']);
        }

        // Log wallet update (without sensitive data)
        Log::info('Updating Web3 wallet', [
            'wallet_id' => $wallet->id,
            'network' => $data['network'] ?? 'unchanged',
            'user_id' => Auth::id(),
        ]);

        $wallet->update($data);

        return $wallet;
    }

    /**
     * Delete a Web3 wallet.
     *
     * @param Web3Wallet $wallet
     * @return bool
     * @throws \Exception
     */
    public function delete(Web3Wallet $wallet): bool
    {
        // Check if the wallet is connected to a MEXC account
        if ($wallet->mexcAccount) {
            throw new \Exception('Cannot delete wallet that is linked to a MEXC account.');
        }

        // Log wallet deletion
        Log::info('Deleting Web3 wallet', [
            'wallet_id' => $wallet->id,
            'address' => $wallet->address,
            'user_id' => Auth::id(),
        ]);

        return $wallet->delete() ?? false;
    }

    /**
     * Generate a random Web3 wallet (server-side implementation if needed).
     * Note: This is primarily handled by the frontend JS, but included here for completeness.
     *
     * @param string $network
     * @return array
     */
    public function generateWallet(string $network): array
    {
        // This would typically be handled by the frontend JS using ethers.js
        // But we include a stub here in case server-side generation is needed in the future

        Log::info('Server-side wallet generation not implemented - falling back to client-side generation');

        return [
            'address' => null,
            'seed_phrase' => null,
            'private_key' => null,
            'error' => 'Server-side wallet generation not implemented.'
        ];
    }
}