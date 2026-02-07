<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LocalTestSeeder extends Seeder
{

    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // payment_methodsのダミーデータの作成
        $this->call(PaymentMethodSeeder::class);

        // product_categoriesのダミーデータ作成
        $this->call(ProductCategorySeeder::class);

        // product_conditionsのダミーデータ作成
        $this->call(ProductConditionSeeder::class);

        // 検証用ユーザーの作成
        User::create([
            'name' => '取引有ユーザー',
            'email' => 'test1@example.com',
            'password' => Hash::make('test1234'),
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市1-2-3',
            'building' => 'テストマンション101',
            'email_verified_at' => now(),
            'profile_completed_at' => now(),
        ]);
        User::create([
            'name' => '未取引ユーザー',
            'email' => 'test2@example.com',
            'password' => Hash::make('test1234'),
            'postal_code' => '987-6543',
            'address' => 'テスト県テスト市5-10-20',
            'building' => 'テストマンション501',
            'email_verified_at' => now(),
            'profile_completed_at' => now(),
        ]);

        // usersのダミーデータ作成
        User::factory(10)->create();

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
