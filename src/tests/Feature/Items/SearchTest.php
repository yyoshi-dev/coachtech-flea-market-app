<?php

namespace Tests\Feature\Items;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductLike;
use App\Models\User;
use Database\Seeders\ProductConditionSeeder;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 「商品名」で部分一致検索ができる
     * ゲストの場合
     */
    public function testGuestCanSearchProductsByKeyword()
    {
        // 商品状態データの作成
        $this->seed(ProductConditionSeeder::class);

        // 商品データの作成
        Product::factory()
            ->count(2)
            ->sequence(
                ['name' => 'TEST_SAMPLE_ITEM'],
                ['name' => 'TEST_KEYWORD_ITEM'],
            )
            ->create();

        // 部分検索を実施し表示を確認
        $response = $this->get('/?keyword=KEY');
        $response->assertOk();

        // キーワードを含む商品が表示されている事を確認
        $response->assertSeeText('TEST_KEYWORD_ITEM');

        // キーワードを含まない商品が表示されていない事を確認
        $response->assertDontSeeText('TEST_SAMPLE_ITEM');
    }

    /**
     * 「商品名」で部分一致検索ができる
     * ログインユーザーの場合
     */
    public function testLoggedInUserCanSearchProductsByKeywordExcludingOwnProducts()
    {
        // ユーザーを作成
        $me = User::factory()->create();
        $other = User::factory()->create();

        // 商品状態データの作成
        $this->seed(ProductConditionSeeder::class);

        // 他者の商品
        Product::factory()
            ->count(2)
            ->state(['user_id' => $other->id])
            ->sequence(
                ['name' => 'TEST_SAMPLE_ITEM'],
                ['name' => 'TEST_KEYWORD_ITEM'],
            )
            ->create();

        // 自分の商品
        Product::factory()->create([
            'user_id' => $me->id,
            'name' => 'TEST_KEYWORD_MY_ITEM',
        ]);

        // ログインして部分検索を実施し表示を確認
        $response = $this->actingAs($me)->get('/?keyword=KEY');
        $response->assertOk();

        // キーワードを含む商品が表示されている事を確認
        $response->assertSeeText('TEST_KEYWORD_ITEM');

        // キーワードを含まない商品が表示されていない事を確認
        $response->assertDontSeeText('TEST_SAMPLE_ITEM');

        // キーワードを含む自己出品の商品が表示されていない事を確認
        $response->assertDontSeeText('TEST_KEYWORD_MY_ITEM');
    }

    /**
     * 検索状態がマイリストでも保持されている
     */
    public function testSearchKeywordIsPreservedInMyList()
    {
        // ユーザーを作成
        $me = User::factory()->create();
        $other = User::factory()->create();

        // 商品状態データの作成
        $this->seed(ProductConditionSeeder::class);

        // いいね用商品といいねしない商品を作成
        $likedProduct = Product::factory()->create([
            'user_id' => $other->id,
            'name' => 'TEST_KEYWORD_LIKED',
        ]);
        Product::factory()->create([
            'user_id' => $other->id,
            'name' => 'TEST_KEYWORD_NOT_LIKED',
        ]);

        // いいねデータの作成
        ProductLike::factory()->create([
            'user_id' => $me->id,
            'product_id' => $likedProduct->id
        ]);

        // ログインして商品一覧画面で部分検索を実施し表示を確認
        $response = $this->actingAs($me)->get('/?keyword=KEY');
        $response->assertOk();

        // キーワードを含む商品が表示されている事を確認
        $response->assertSeeText('TEST_KEYWORD_LIKED');
        $response->assertSeeText('TEST_KEYWORD_NOT_LIKED');

        // マイリストにkeywordが引き継がれるかの確認
        $response->assertSee('tab=mylist', false);
        $response->assertSee('keyword=KEY', false);

        // キーワードを付けてマイリストにアクセスし表示を確認
        $responseMyList = $this->actingAs($me)->get('/?tab=mylist&keyword=KEY');
        $responseMyList->assertOk();

        // マイリストページの検索欄にキーワードが入っているかの確認
        $responseMyList->assertSee('name="keyword"', false);
        $responseMyList->assertSee('value="KEY"', false);

        // キーワードを含み、かついいねした商品が表示されている事を確認
        $responseMyList->assertSeeText('TEST_KEYWORD_LIKED');

        // 上記以外の商品が表示されていない事を確認
        $responseMyList->assertDontSeeText('TEST_KEYWORD_NOT_LIKED');
    }
}
