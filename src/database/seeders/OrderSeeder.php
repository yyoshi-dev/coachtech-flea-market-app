<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('orders')->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'product_id' => 4,
                'postal_code' => '123-4567',
                'address' => 'テスト県テスト市1-2-3',
                'building' => 'テストマンション101',
                'payment_method_id' => 1
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'product_id' => 6,
                'postal_code' => '123-4567',
                'address' => 'テスト県テスト市1-2-3',
                'building' => 'テストマンション101',
                'payment_method_id' => 2
            ],
        ]);
    }
}
