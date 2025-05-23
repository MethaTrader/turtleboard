<?php

namespace App\Services;

use App\Models\EmailAccount;
use Illuminate\Support\Facades\Auth;

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
        $data['user_id'] = Auth::id();

        // Create the email account
        $emailAccount = EmailAccount::create($data);

        // Additional logic if needed (e.g., logging, related records)

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

        $emailAccount->update($data);

        // Additional logic if needed

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