<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Web3Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class Web3WalletFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Web3Wallet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a random Ethereum-like address
        $address = '0x' . Str::random(40);

        // Generate a random seed phrase (12 words)
        $words = [];
        for ($i = 0; $i < 12; $i++) {
            $words[] = $this->faker->word();
        }
        $seedPhrase = implode(' ', $words);

        return [
            'address' => $address,
            'seed_phrase' => Crypt::encrypt($seedPhrase),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Generate a specific blockchain address format.
     */
    public function ethereum(): static
    {
        return $this->state(fn (array $attributes) => [
            'address' => '0x' . Str::random(40),
        ]);
    }

    /**
     * Generate a Bitcoin-like address.
     */
    public function bitcoin(): static
    {
        return $this->state(fn (array $attributes) => [
            'address' => '1' . Str::random(33),
        ]);
    }
}