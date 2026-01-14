<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategoryRelationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product_category_relations')->insert([
            ['product_id' => 1, 'product_category_id' => 5],
            ['product_id' => 1, 'product_category_id' => 12],
            ['product_id' => 2, 'product_category_id' => 2],
            ['product_id' => 3, 'product_category_id' => 10],
            ['product_id' => 4, 'product_category_id' => 1],
            ['product_id' => 4, 'product_category_id' => 5],
            ['product_id' => 5, 'product_category_id' => 2],
            ['product_id' => 6, 'product_category_id' => 2],
            ['product_id' => 7, 'product_category_id' => 1],
            ['product_id' => 7, 'product_category_id' => 4],
            ['product_id' => 8, 'product_category_id' => 10],
            ['product_id' => 9, 'product_category_id' => 10],
            ['product_id' => 10, 'product_category_id' => 6]
        ]);
    }
}
