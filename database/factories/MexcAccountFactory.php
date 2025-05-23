<?php

namespace Database\Factories;

use App\Models\EmailAccount;
use App\Models\MexcAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

class MexcAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MexcAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email_account_id' => EmailAccount::factory(),
            'password' => Crypt::encrypt('mexc_password_' . $this->faker->word()),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'suspended']),
        ];
    }

    /**
     * Indicate that the MEXC account is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the MEXC account is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the MEXC account is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    /**
     * Define a relationship with a specific email account.
     */
    public function forEmailAccount(EmailAccount $emailAccount): static
    {
        return $this->state(fn (array $attributes) => [
            'email_account_id' => $emailAccount->id,
            'user_id' => $emailAccount->user_id,
        ]);
    }
}