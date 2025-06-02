<?php

namespace Database\Factories;

use App\Models\EmailAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class EmailAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $providers = ['Gmail', 'Outlook', 'Yahoo', 'iCloud'];
        $provider = $this->faker->randomElement($providers);

        $domains = [
            'Gmail' => 'gmail.com',
            'Outlook' => 'outlook.com',
            'Yahoo' => 'yahoo.com',
            'iCloud' => 'icloud.com',
        ];

        // Add more randomness to ensure uniqueness
        $username = $this->faker->userName() . '.' . Str::random(8);
        $domain = $domains[$provider];
        $email = $username . '@' . $domain;

        return [
            'provider' => $provider,
            'email_address' => $email,
            'password' => Crypt::encrypt('password123'), // Pre-encrypted for factory use
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'suspended']),
        ];
    }

    /**
     * Indicate that the email account is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Set a specific provider for the email account.
     */
    public function provider(string $provider): static
    {
        $domains = [
            'Gmail' => 'gmail.com',
            'Outlook' => 'outlook.com',
            'Yahoo' => 'yahoo.com',
            'iCloud' => 'icloud.com',
        ];

        // Add more randomness to ensure uniqueness
        $username = $this->faker->userName() . '.' . Str::random(8);
        $domain = $domains[$provider] ?? 'example.com';
        $email = $username . '@' . $domain;

        return $this->state(fn (array $attributes) => [
            'provider' => $provider,
            'email_address' => $email,
        ]);
    }
}