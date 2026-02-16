<?php

namespace Tests\Feature\Items;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductLike;
use App\Models\User;
use Database\Seeders\ProductConditionSeeder;

class MyListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * いいねした商品だけが表示される
     */
    public function testUserSeesOnlyLikedItemsInMyList()
    {
        // ユーザーを作成
        $me = User::factory()->create();
        $other = User::factory()->create();

        // 商品状態データの作成
        $this->seed(ProductConditionSeeder::class);

        // いいね用商品といいねしない商品を作成
        $likedProducts = Product::factory()
            ->count(2)
            ->state(['user_id' => $other->id])
            ->sequence(
                ['name' => 'TEST_LIKED_1'],
                ['name' => 'TEST_LIKED_2'],
            )
            ->create();
        $notLikedProducts = Product::factory()
            ->count(2)
            ->state(['user_id' => $other->id])
            ->sequence(
                ['name' => 'TEST_NOT_LIKED_1'],
                ['name' => 'TEST_NOT_LIKED_2'],
            )
            ->create();

        // いいねデータの作成
        foreach($likedProducts as $p) {
            ProductLike::factory()->create([
                'user_id' => $me->id,
                'product_id' => $p->id
            ]);
        }

        // ログインし商品一覧画面のマイリストにアクセスして表示を確認
        $response = $this->actingAs($me)->get('/?tab=mylist');
        $response->assertOk();

        // いいねした商品が表示される事を確認
        foreach($likedProducts as $likedProduct) {
            $response->assertSeeText($likedProduct->name);
        }

        // いいねしていない商品が表示されない事を確認
        foreach($notLikedProducts as $notLikedProduct) {
            $response->assertDontSeeText($notLikedProduct->name);
        }
    }

    /**
     * 購入済み商品は「Sold」と表示される
     */
    public function testSoldItemDisplaySoldLabelInMyList()
    {
        // ユーザーを作成
        $me = User::factory()->create();
        $other = User::factory()->create();

        // 商品状態データの作成
        $this->seed(ProductConditionSeeder::class);

        // 未購入・購入済み商品データの両方を作成
        $normalProducts = Product::factory()
            ->count(2)
            ->state(['user_id' => $other->id])
            ->sequence(
                ['name' => 'TEST_NORMAL_1'],
                ['name' => 'TEST_NORMAL_2'],
            )
            ->create();
        $soldProducts = Product::factory()
            ->count(2)
            ->sold()
            ->state(['user_id' => $other->id])
            ->sequence(
                ['name' => 'TEST_SOLD_1'],
                ['name' => 'TEST_SOLD_2'],
            )
            ->create();

        // いいねデータの作成
        foreach($normalProducts->merge($soldProducts) as $product) {
            ProductLike::factory()->create([
                'user_id' => $me->id,
                'product_id' => $product->id,
            ]);
        }

        // ログインし商品一覧画面のマイリストにアクセスして表示を確認
        $response = $this->actingAs($me)->get('/?tab=mylist');
        $response->assertOk();

        // 全ての商品が表示される事を確認
        foreach ($normalProducts->merge($soldProducts) as $product) {
            $response->assertSeeText($product->name);
        }

        // 購入済み商品に「Sold」ラベルが表示される事を確認
        // Soldラベルの数が購入済み商品の数と一致する事を確認
        $html = $response->getContent();
        $this->assertSame(
            $soldProducts->count(),
            substr_count($html, 'data-testid="sold-badge"')
        );
    }

    // /**
    //  * 未認証の場合は何も表示されない
    //  */
    public function testGuestSeesNoItemsInMyList()
    {
        // ユーザーを作成
        $seller = User::factory()->create();
        $liker = User::factory()->create();

        // 商品状態データの作成
        $this->seed(ProductConditionSeeder::class);

        // 商品データの作成
        $products = Product::factory()
            ->count(2)
            ->state(['user_id' => $seller->id])
            ->sequence(
                ['name' => 'TEST_ITEM_1'],
                ['name' => 'TEST_ITEM_2'],
            )
            ->create();

        // いいねデータの作成
        foreach($products as $product) {
            ProductLike::factory()->create([
                'user_id' => $liker->id,
                'product_id' => $product->id,
            ]);
        }

        // ログインせずに商品一覧画面のマイリストにアクセスして表示を確認
        $response = $this->get('/?tab=mylist');
        $response->assertOk();

        // 何も表示されない事を確認
        foreach($products as $product) {
            $response->assertDontSeeText($product->name);
        }
    }
}
