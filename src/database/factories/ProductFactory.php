<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'brand_name' => fake()->optional()->company(),
            'description' => fake()->text(200),
            'price' => fake()->numberBetween(100, 100000),
            'product_condition_id' => ProductCondition::query()->firstOrFail()->id,
            'product_image_path' => 'products/dummy_product.jpg',
            'sold_at' => null,
        ];
    }

    // 購入済みの状態
    public function sold(): static
    {
        return $this->state(fn () => [
            'sold_at' => now(),
        ]);
    }
}
