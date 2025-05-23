<?php

namespace App\Services;

use App\Models\EmailAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmailAccountService
{
    /**
     * Create a new email account.
     *
     * @param array $data
     * @return EmailAccount
     */
    public function create(array $data): EmailAccount
    {
        // Ensure user_id is set
        $data['user_id'] = Auth::id();

        // Handle empty proxy_id
        if (empty($data['proxy_id'])) {
            $data['proxy_id'] = null;
        }

        // Ensure status is set
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        // Log the data being inserted for debugging
        Log::info('Creating email account with data:', $data);

        // Create the email account
        $emailAccount = EmailAccount::create($data);

        return $emailAccount;
    }

    /**
     * Update an existing email account.
     *
     * @param EmailAccount $emailAccount
     * @param array $data
     * @return EmailAccount
     */
    public function update(EmailAccount $emailAccount, array $data): EmailAccount
    {
        // Handle password separately if it's not being updated
        if (!isset($data['password']) || empty($data['password'])) {
            unset($data['password']);
        }

        // Handle empty proxy_id
        if (isset($data['proxy_id']) && empty($data['proxy_id'])) {
            $data['proxy_id'] = null;
        }

        $emailAccount->update($data);

        return $emailAccount;
    }

    /**
     * Delete an email account.
     *
     * @param EmailAccount $emailAccount
     * @return bool
     */
    public function delete(EmailAccount $emailAccount): bool
    {
        // Check for relationships before deleting
        if ($emailAccount->mexcAccount) {
            throw new \Exception('Cannot delete email account that is linked to a MEXC account.');
        }

        return $emailAccount->delete();
    }
}