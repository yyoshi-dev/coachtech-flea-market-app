<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'postal_code' => fake()->numerify('###-####'),
            'address' => fake()->prefecture() . ' ' . fake()->city() . ' ' . fake()->streetAddress(),
            'building' => fake()->secondaryAddress(),
            'payment_method_id' => PaymentMethod::inRandomOrder()->value('id'),
        ];
    }
}
