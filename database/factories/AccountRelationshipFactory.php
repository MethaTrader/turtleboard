<?php

namespace Database\Factories;

use App\Models\AccountRelationship;
use App\Models\EmailAccount;
use App\Models\MexcAccount;
use App\Models\Proxy;
use App\Models\User;
use App\Models\Web3Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountRelationshipFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AccountRelationship::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'proxy_id' => Proxy::factory(),
            'email_account_id' => EmailAccount::factory(),
            'mexc_account_id' => MexcAccount::factory(),
            'web3_wallet_id' => Web3Wallet::factory(),
            'created_by' => User::factory(),
        ];
    }
}