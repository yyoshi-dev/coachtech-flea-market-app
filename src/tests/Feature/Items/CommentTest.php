<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductComment;
use App\Models\User;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\ProductConditionSeeder;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログイン済みのユーザーはコメントを送信できる
     */
    public function test_logged_in_user_can_post_comment()
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

        // 送信前のコメント数が0件である事を確認
        // DB上での確認
        $this->assertSame(0, ProductComment::count());
        // 画面上での確認
        $this->actingAs($user)
            ->get("/item/{$product->id}")
            ->assertOk()
            ->assertSeeInOrder([
                'data-testid="comments-count"',
                '>',
                '0',
                '<',
            ], false);

        // ログインし、商品詳細画面にてコメントを送信
        $this->actingAs($user)
            ->post("/item/{$product->id}/comment", ['comment' => 'test'])
            ->assertRedirect("/item/{$product->id}");

        // コメントした商品として登録されている事を確認
        $this->assertDatabaseHas('product_comments', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'comment' => 'test',
        ]);

        // コメント送信後のコメント数が1件に増加した事を確認
        // DB上での確認
        $this->assertSame(1, ProductComment::where('product_id', $product->id)->count());
        // 画面上での確認
        $this->actingAs($user)
            ->get("/item/{$product->id}")
            ->assertOk()
            ->assertSeeInOrder([
                'data-testid="comments-count"',
                '>',
                '1',
                '<',
            ], false);
    }

    /**
     * ログイン前のユーザーはコメントを送信できない
     */
    public function test_guest_cannot_post_comment()
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

        // 送信前のコメント数が0件である事を確認
        $this->assertDatabaseCount('product_comments', 0);

        // ログインせずに商品詳細画面にてコメントを送信する場合、ログインページにリダイレクトされる事を確認
        $response = $this->post("/item/{$product->id}/comment", ['comment' => 'test']);
        $response->assertRedirect('/login');

        // 送信後のコメント数が増えていない事を確認
        $this->assertDatabaseCount('product_comments', 0);
    }

    /**
     * コメントが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_comment_is_required_and_validation_error_is_displayed()
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

        // 送信前のコメント数が0件である事を確認
        $this->assertDatabaseCount('product_comments', 0);

        // ログインし、商品詳細画面にてコメント無しでコメントを送信する
        $response = $this->actingAs($user)
            ->post("/item/{$product->id}/comment", ['comment' => '']);

        // バリデーションメッセージを確認
        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'comment' => 'コメントを入力してください'
        ]);

        // 送信後のコメント数が増えていない事を確認
        $this->assertDatabaseCount('product_comments', 0);
    }

    /**
     * コメントが255字以上の場合、バリデーションメッセージが表示される
     */
    public function test_comment_max_length_validation_error_is_displayed()
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

        // 送信前のコメント数が0件である事を確認
        $this->assertDatabaseCount('product_comments', 0);

        // ログインし、商品詳細画面にて255字以上でコメントを送信する
        $tooLongComment = str_repeat('a', 256);
        $response = $this->actingAs($user)
            ->post("/item/{$product->id}/comment", ['comment' => $tooLongComment]);

        // バリデーションメッセージを確認
        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'comment' => 'コメントは255文字以内で入力してください'
        ]);

        // 送信後のコメント数が増えていない事を確認
        $this->assertDatabaseCount('product_comments', 0);
    }
}
