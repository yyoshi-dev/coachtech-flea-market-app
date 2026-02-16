<?php

namespace Tests\Feature\Items;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductConditionSeeder;

class ItemListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 全商品を取得できる
     */
    public function testAllItemsAreDisplayed()
    {
        // 商品状態データの作成
        $this->seed(ProductConditionSeeder::class);

        // 商品データの作成
        $products = Product::factory()->count(3)->create();

        // 商品一覧画面の表示を確認
        $response = $this->get('/');
        $response->assertOk();

        // 全ての商品が表示される事を確認
        foreach ($products as $product) {
            $response->assertSeeText($product->name);
        }
    }

    /**
     * 購入済み商品は「Sold」と表示される
     */
    public function testSoldItemDisplaySoldLabel()
    {
        // 商品状態データの作成
        $this->seed(ProductConditionSeeder::class);

        // 未購入・購入済み商品データの両方を作成
        $normalProducts = Product::factory()
            ->count(2)
            ->sequence(
                ['name' => 'TEST_NORMAL_1'],
                ['name' => 'TEST_NORMAL_2'],
            )
            ->create();
        $soldProducts = Product::factory()
            ->count(2)
            ->sold()
            ->sequence(
                ['name' => 'TEST_SOLD_1'],
                ['name' => 'TEST_SOLD_2'],
            )
            ->create();

        // 商品一覧画面の表示を確認
        $response = $this->get('/');
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

    /**
     * 自分が出品した商品は表示されない
     */
    public function testUserDoesNotSeeOwnItemsInList()
    {
        // ユーザーを作成
        $me = User::factory()->create();
        $other = User::factory()->create();

        // 商品状態データの作成
        $this->seed(ProductConditionSeeder::class);

        // 自分で出品した商品とそれ以外の商品を作成
        $myProducts = Product::factory()
            ->count(2)
            ->state(['user_id' => $me->id])
            ->sequence(
                ['name' => 'TEST_MY_ITEM_1'],
                ['name' => 'TEST_MY_ITEM_2'],
            )
            ->create();
        $otherProducts = Product::factory()
            ->count(2)
            ->state(['user_id' => $other->id])
            ->sequence(
                ['name' => 'TEST_OTHER_ITEM_1'],
                ['name' => 'TEST_OTHER_ITEM_2'],
            )
            ->create();

        // ログインし商品一覧画面にアクセスして表示を確認
        $response = $this->actingAs($me)->get('/');
        $response->assertOk();

        // 他者が出品した商品は表示される事を確認
        foreach ($otherProducts as $otherProduct) {
            $response->assertSeeText($otherProduct->name);
        }

        // 自分が出品した商品は表示されない事を確認
        foreach ($myProducts as $myProduct) {
            $response->assertDontSeeText($myProduct->name);
        }
    }
}
