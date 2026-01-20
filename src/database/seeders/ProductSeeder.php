<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // テーブル挿入用の配列を作成
        $products = [
            [
                'id' => 1,
                'user_id' => 1,
                'name' => '腕時計',
                'brand_name' => 'Rolax',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'price' => 15000,
                'product_condition_id' => 1,
                'likes_count' => 0,
                'comments_count' => 0,
                'sold_at' => null
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'name' => 'HDD',
                'brand_name' => '西芝',
                'description' => '高速で信頼性の高いハードディスク',
                'price' => 5000,
                'product_condition_id' => 2,
                'likes_count' => 0,
                'comments_count' => 0,
                'sold_at' => null
            ],
            [
                'id' => 3,
                'user_id' => 1,
                'name' => '玉ねぎ3束',
                'brand_name' => 'なし',
                'description' => '新鮮な玉ねぎ3束のセット',
                'price' => 300,
                'product_condition_id' => 3,
                'likes_count' => 0,
                'comments_count' => 0,
                'sold_at' => null
            ],
            [
                'id' => 4,
                'user_id' => 2,
                'name' => '革靴',
                'brand_name' => '',
                'description' => 'クラシックなデザインの革靴',
                'price' => 4000,
                'product_condition_id' => 4,
                'likes_count' => 0,
                'comments_count' => 0,
                'sold_at' => now()
            ],
            [
                'id' => 5,
                'user_id' => 2,
                'name' => 'ノートPC',
                'brand_name' => '',
                'description' => '高性能なノートパソコン',
                'price' => 45000,
                'product_condition_id' => 1,
                'likes_count' => 0,
                'comments_count' => 0,
                'sold_at' => null
            ],
            [
                'id' => 6,
                'user_id' => 2,
                'name' => 'マイク',
                'brand_name' => 'なし',
                'description' => '高音質のレコーディング用マイク',
                'price' => 8000,
                'product_condition_id' => 2,
                'likes_count' => 0,
                'comments_count' => 0,
                'sold_at' => now()
            ],
            [
                'id' => 7,
                'user_id' => 3,
                'name' => 'ショルダーバッグ',
                'brand_name' => '',
                'description' => 'おしゃれなショルダーバッグ',
                'price' => 3500,
                'product_condition_id' => 3,
                'likes_count' => 0,
                'comments_count' => 0,
                'sold_at' => null
            ],
            [
                'id' => 8,
                'user_id' => 3,
                'name' => 'タンブラー',
                'brand_name' => 'なし',
                'description' => '使いやすいタンブラー',
                'price' => 500,
                'product_condition_id' => 4,
                'likes_count' => 0,
                'comments_count' => 0,
                'sold_at' => null
            ],
            [
                'id' => 9,
                'user_id' => 4,
                'name' => 'コーヒーミル',
                'brand_name' => 'Starbacks',
                'description' => '手動のコーヒーミル',
                'price' => 4000,
                'product_condition_id' => 1,
                'likes_count' => 0,
                'comments_count' => 0,
                'sold_at' => null
            ],
            [
                'id' => 10,
                'user_id' => 5,
                'name' => 'メイクセット',
                'brand_name' => '',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'product_condition_id' => 2,
                'likes_count' => 0,
                'comments_count' => 0,
                'sold_at' => null
            ],
        ];

        // 配列への画像パス追加、画像のstorageディレクトリへのコピー
        $finalProducts = [];

        foreach ($products as $product) {

            $id = $product['id'];
            $imagePath = sprintf('products/dummy_product_%02d.jpg', $id);

            // storageディレクトリへのコピー
            Storage::disk('public')->put(
                $imagePath,
                file_get_contents(
                    database_path(sprintf('seed_images/products/dummy_product_%02d.jpg', $id))
                )
            );

            // products配列へのパスの追加
            $product['product_image_path'] = $imagePath;
            $finalProducts[] = $product;
        }

        // テーブルへの追加
        DB::table('products')->insert($finalProducts);
    }
}
