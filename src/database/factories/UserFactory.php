<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'name' => fake()->name(),
            'name' => (function () {
                $tries = 0;
                do {
                    $name = $this->faker->name();
                    $tries++;
                } while (mb_strlen($name) > 20 && $tries <10);
                return mb_substr($name, 0, 20);
            })->call($this),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'postal_code' => $this->faker->numerify('###-####'),
            'address' => $this->faker->prefecture() . ' ' . $this->faker->city() . ' ' . $this->faker->streetAddress(),
            'building' => $this->faker->secondaryAddress(),
            'email_verified_at' => now(),
            'profile_completed_at' => now(),
        ];
    }
}
