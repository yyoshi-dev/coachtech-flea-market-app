<?php

namespace Tests\Feature\Items;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductLike;
use App\Models\User;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\ProductConditionSeeder;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * いいねアイコンを押下することによって、いいねした商品として登録することができる
     */
    public function test_user_can_like_product_and_like_count_increases()
    {
        // ユーザーを作成
        $user = User::factory()->create();
        $other = User::factory()->create();

        // 商品状態データ、商品カテゴリデータの作成
        $this->seed(ProductConditionSeeder::class);
        $this->seed(ProductCategorySeeder::class);

        // 商品データの作成
        $product = Product::factory()->create([
            'user_id' => $other->id,
        ]);

        // 商品カテゴリの紐付け
        $categoryId = ProductCategory::where('name', 'ファッション')->value('id');
        $this->assertNotNull($categoryId);
        $product->productCategories()->attach($categoryId);

        // いいね実行前のいいね数が0件である事を確認
        // DB上での確認
        $this->assertDatabaseCount('product_likes', 0);
        // 画面上での確認
        $this->actingAs($user)
            ->get("/item/{$product->id}")
            ->assertOk()
            ->assertSeeInOrder([
                'data-testid="likes-count"',
                '>',
                '0',
                '<',
            ], false);

        // ログインして商品詳細画面にていいねを実行
        $this->actingAs($user)
            ->post("/item/{$product->id}/like")
            ->assertRedirect("/item/{$product->id}");

        // いいねした商品として登録されている事を確認
        $this->assertDatabaseHas('product_likes', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // いいね実行後のいいね数が1件に増加した事を確認
        // DB上での確認
        $this->assertSame(1, ProductLike::where('product_id', $product->id)->count());
        // 画面上での確認
        $this->actingAs($user)
            ->get("/item/{$product->id}")
            ->assertOk()
            ->assertSeeInOrder([
                'data-testid="likes-count"',
                '>',
                '1',
                '<',
            ], false);
    }

    /**
     * 追加済みのアイコンは色が変化する
     */
    public function test_liked_icon_shows_active_state_for_user()
    {
        // ユーザーを作成
        $user = User::factory()->create();
        $other = User::factory()->create();

        // 商品状態データ、商品カテゴリデータの作成
        $this->seed(ProductConditionSeeder::class);
        $this->seed(ProductCategorySeeder::class);

        // 商品データの作成
        $product = Product::factory()->create([
            'user_id' => $other->id,
        ]);

        // 商品カテゴリの紐付け
        $categoryId = ProductCategory::where('name', 'ファッション')->value('id');
        $this->assertNotNull($categoryId);
        $product->productCategories()->attach($categoryId);

        // 商品詳細画面にアクセスして、いいねマークの色がデフォルトであるを確認
        $response = $this->actingAs($user)->get("/item/{$product->id}");
        $response->assertOk();
        $response->assertSee('alt="heart-logo-default"', false);
        $response->assertDontSee('alt="heart-logo-pink"', false);

        // ログインして商品詳細画面にていいねを実行
        $this->actingAs($user)
            ->post("/item/{$product->id}/like")
            ->assertRedirect("/item/{$product->id}");

        // いいねした商品として登録されている事を確認
        $this->assertDatabaseHas('product_likes', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // 商品詳細画面にアクセスして、いいねマークの色が変化している事を確認
        $response = $this->actingAs($user)->get("/item/{$product->id}");
        $response->assertOk();
        $response->assertSee('alt="heart-logo-pink"', false);
        $response->assertDontSee('alt="heart-logo-default"', false);
    }

    /**
     * 再度いいねアイコンを押下することによって、いいねを解除することができる
     */
    public function test_user_can_unlike_product_and_like_count_decreases()
    {
        // ユーザーを作成
        $user = User::factory()->create();
        $other = User::factory()->create();

        // 商品状態データ、商品カテゴリデータの作成
        $this->seed(ProductConditionSeeder::class);
        $this->seed(ProductCategorySeeder::class);

        // 商品データの作成
        $product = Product::factory()->create([
            'user_id' => $other->id,
        ]);

        // 商品カテゴリの紐付け
        $categoryId = ProductCategory::where('name', 'ファッション')->value('id');
        $this->assertNotNull($categoryId);
        $product->productCategories()->attach($categoryId);

        // いいねデータの作成
        ProductLike::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // いいね実行前のいいね数が1件である事を確認
        // DB上での確認
        $this->assertSame(1, ProductLike::where('product_id', $product->id)->count());
        // 画面上での確認
        $this->actingAs($user)
            ->get("/item/{$product->id}")
            ->assertOk()
            ->assertSeeInOrder([
                'data-testid="likes-count"',
                '>',
                '1',
                '<',
            ], false);

        // ログインして商品詳細画面にていいね解除を実行
        $this->actingAs($user)
            ->post("/item/{$product->id}/like")
            ->assertRedirect("/item/{$product->id}");

        // いいねした商品として登録されていない事を確認
        $this->assertDatabaseMissing('product_likes', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // いいね実行後のいいね数が0件に減少した事を確認
        // DB上での確認
        $this->assertSame(0, ProductLike::where('product_id', $product->id)->count());
        // 画面上での確認
        $this->actingAs($user)
            ->get("/item/{$product->id}")
            ->assertOk()
            ->assertSeeInOrder([
                'data-testid="likes-count"',
                '>',
                '0',
                '<',
            ], false);
    }

    /**
     * (オプション) ゲストユーザーはいいねできない
     */
    public function test_guest_cannot_like_product()
    {
        // ユーザーを作成
        $other = User::factory()->create();

        // 商品状態データ、商品カテゴリデータの作成
        $this->seed(ProductConditionSeeder::class);
        $this->seed(ProductCategorySeeder::class);

        // 商品データの作成
        $product = Product::factory()->create([
            'user_id' => $other->id,
        ]);

        // 商品カテゴリの紐付け
        $categoryId = ProductCategory::where('name', 'ファッション')->value('id');
        $this->assertNotNull($categoryId);
        $product->productCategories()->attach($categoryId);

        // いいね前のいいね数が0件である事を確認
        $this->assertDatabaseCount('product_likes', 0);

        // ログインせずに商品詳細画面にていいねを実行する場合、ログインページにリダイレクトされる事を確認
        $response = $this->post("/item/{$product->id}/like");
        $response->assertRedirect('/login');

        // いいね数が増えていない事を確認
        $this->assertDatabaseCount('product_likes', 0);
    }
}
