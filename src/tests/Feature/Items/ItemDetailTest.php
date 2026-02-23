<?php

namespace Tests\Feature\Items;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductLike;
use App\Models\ProductComment;
use App\Models\User;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\ProductConditionSeeder;

class ItemDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 必要な情報が表示される
     * (商品画像、商品名、ブランド名、価格、いいね数、コメント数、商品説明、
     * 商品情報 (カテゴリ、商品の状態)、コメント数、コメントしたユーザー情報、コメント内容)
     */
    public function test_product_details_are_displayed_correctly()
    {
        // ユーザーを作成
        $user = User::factory()->create();
        $seller = User::factory()->create();
        $liker = User::factory()->create();
        $other = User::factory()->create();

        // 商品状態データ、商品カテゴリデータの作成
        $this->seed(ProductConditionSeeder::class);
        $this->seed(ProductCategorySeeder::class);

        // 商品データの作成
        $product = Product::factory()->create([
            'user_id' => $seller->id,
        ]);

        // 商品カテゴリの紐付け
        $categoryIds = ProductCategory::whereIn('name', ['ファッション', 'メンズ'])
            ->pluck('id')
            ->all();
        $this->assertCount(2, $categoryIds);    // カテゴリが正しく取れている事を念の為確認
        $product->productCategories()->sync($categoryIds);

        // いいねデータの作成
        ProductLike::factory()
            ->count(3)
            ->state(['product_id' => $product->id])
            ->sequence(
                ['user_id' => $user->id,],
                ['user_id' => $other->id,],
                ['user_id' => $liker->id,],
            )
            ->create();

        // コメントデータの作成
        $productComments = ProductComment::factory()
            ->count(2)
            ->state(['product_id' => $product->id])
            ->sequence(
                ['user_id' => $user->id,],
                ['user_id' => $other->id,],
            )
            ->create();

        // リレーションの再取得
        $product = $product->fresh([
            'productLikes', 'productComments', 'productCategories', 'productCondition'
        ]);

        // 商品詳細画面の表示を確認
        $response = $this->get("/item/{$product->id}");
        $response->assertOk();

        // 基本情報 (商品名、ブランド名、商品説明、価格)の検証
        $response->assertSeeText($product->name);
        $response->assertSeeText($product->brand_name);
        $response->assertSeeText(number_format($product->price));
        $response->assertSeeText($product->description);

        // 商品画像の検証
        $response->assertSee('src="' . asset('storage/' . $product->product_image_path) . '"', false);

        // カテゴリ・商品の状態の検証
        foreach ($product->productCategories as $category) {
            $response->assertSeeText($category->name);
        }
        $response->assertSeeText($product->productCondition->name);

        // いいね及びコメントマークしたの各カウント数の検証
        $likesCount = (string) $product->productLikes->count();
        $commentsCount = (string) $product->productComments->count();
        $response->assertSeeInOrder([
            'data-testid="likes-count"',
            '>',
            $likesCount,
            '<',
        ], false);
        $response->assertSeeInOrder([
            'data-testid="comments-count"',
            '>',
            $commentsCount,
            '<'
        ], false);

        // コメントセクション (コメント数、コメントしたユーザー情報、コメント内容)の検証
        $response->assertSee('data-testid="comments-title"', false);
        $response->assertSeeText('コメント (' . $commentsCount . ')');

        $productComments->load('user');
        foreach ($productComments as $comment) {
            $response->assertSeeText($comment->user->name);
            $response->assertSeeText($comment->comment);
        }
    }

    /**
     * 複数選択されたカテゴリが表示されているか
     */
    public function test_multiple_categories_are_displayed()
    {
        // 商品状態データ、商品カテゴリデータの作成
        $this->seed(ProductConditionSeeder::class);
        $this->seed(ProductCategorySeeder::class);

        // 商品データの作成
        $product = Product::factory()->create();

        // 商品カテゴリの紐付け
        $categoryIds = ProductCategory::whereIn('name', ['ファッション', 'メンズ'])
            ->pluck('id')
            ->all();
        $this->assertCount(2, $categoryIds);    // カテゴリが正しく取れている事を念の為確認
        $product->productCategories()->sync($categoryIds);

        // リレーションの再取得
        $product = $product->fresh(['productCategories', 'productCondition']);

        // 商品詳細画面の表示を確認
        $response = $this->get("/item/{$product->id}");
        $response->assertOk();

        // 複数カテゴリが表示されているかを確認
        foreach ($product->productCategories as $category) {
            $response->assertSeeText($category->name);
        }
    }
}
