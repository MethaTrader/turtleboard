<?php

namespace Database\Factories;

use App\Models\Proxy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

class ProxyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Proxy::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a random IP address
        $ip = $this->faker->ipv4();

        // Generate a random port number between 1024 and 65535
        $port = $this->faker->numberBetween(1024, 65535);

        // Sometimes create proxies with authentication, sometimes without
        $hasAuth = $this->faker->boolean(70); // 70% chance of having auth

        return [
            'ip_address' => $ip,
            'port' => $port,
            'username' => $hasAuth ? $this->faker->userName() : null,
            'password' => $hasAuth ? Crypt::encrypt('proxy_password') : null,
            'last_validation_date' => $this->faker->optional(80)->dateTimeBetween('-3 months', 'now'),
            'validation_status' => $this->faker->randomElement(['pending', 'valid', 'invalid']),
            'response_time' => $this->faker->optional(80)->numberBetween(50, 2000),
            'geolocation' => $this->faker->optional(80)->country(),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the proxy is valid.
     */
    public function valid(): static
    {
        return $this->state(fn (array $attributes) => [
            'validation_status' => 'valid',
            'last_validation_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'response_time' => $this->faker->numberBetween(50, 500),
            'geolocation' => $this->faker->country(),
        ]);
    }

    /**
     * Indicate that the proxy is invalid.
     */
    public function invalid(): static
    {
        return $this->state(fn (array $attributes) => [
            'validation_status' => 'invalid',
            'last_validation_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'response_time' => $this->faker->numberBetween(1000, 5000),
            'geolocation' => null,
        ]);
    }

    /**
     * Indicate that the proxy is pending validation.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'validation_status' => 'pending',
            'last_validation_date' => null,
            'response_time' => null,
            'geolocation' => null,
        ]);
    }
}