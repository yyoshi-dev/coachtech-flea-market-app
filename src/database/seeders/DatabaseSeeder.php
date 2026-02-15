<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // usersのダミーデータ作成
        User::factory()->count(10)->create();

        // payment_methodsのダミーデータの作成
        $this->call(PaymentMethodSeeder::class);

        // product_categoriesのダミーデータ作成
        $this->call(ProductCategorySeeder::class);

        // product_conditionsのダミーデータ作成
        $this->call(ProductConditionSeeder::class);

        // productsのダミーデータの作成
        $this->call(ProductSeeder::class);

        // product_category_relationsのダミーデータの作成
        $this->call(ProductCategoryRelationSeeder::class);

        // product_likesのダミーデータの作成
        $this->call(ProductLikeSeeder::class);

        // ordersのダミーデータの作成
        $this->call(OrderSeeder::class);
    }
}
