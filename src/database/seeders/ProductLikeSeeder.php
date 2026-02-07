<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ProductLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product_likes')->insert([
            ['id' => 1, 'user_id' => '1', 'product_id' => 4],
            ['id' => 2, 'user_id' => '1', 'product_id' => 5],
            ['id' => 3, 'user_id' => '1', 'product_id' => 6],
            ['id' => 4, 'user_id' => '1', 'product_id' => 7],
        ]);
    }
}
