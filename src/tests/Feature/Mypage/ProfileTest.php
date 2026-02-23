<?php

namespace Tests\Feature\Mypage;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\ProductConditionSeeder;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 必要な情報が取得できる (プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧)
     */
    public function test_user_profile_page_displays_required_information()
    {
        // ユーザーを作成
        $me = User::factory()->create(['profile_image_path' => 'profiles/dummy_profile.jpg']);
        $other = User::factory()->create();

        // 商品状態、支払い方法データの投入
        $this->seed(PaymentMethodSeeder::class);
        $this->seed(ProductConditionSeeder::class);

        $paymentMethodId = PaymentMethod::query()->value('id');
        $this->assertNotNull($paymentMethodId);

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
        $buyProducts = Product::factory()
            ->count(2)
            ->state(['user_id' => $other->id])
            ->sequence(
                ['name' => 'TEST_BUY_ITEM_1', 'sold_at' => now()],
                ['name' => 'TEST_BUY_ITEM_2', 'sold_at' => now()],
            )
            ->create();

        // オーダーを作成
        foreach ($buyProducts as $buyProduct) {
            Order::factory()->create([
                'user_id' => $me->id,
                'product_id' => $buyProduct->id,
                'payment_method_id' => $paymentMethodId,
            ]);
        }

        // ログインして、マイページを開く
        $response = $this->actingAs($me)
            ->get('/mypage')
            ->assertOk();

        // ユーザー名が表示されている事を確認
        $response->assertSeeText($me->name);

        // プロフィール画像が表示されている事を確認
        $response->assertSee('storage/' . $me->profile_image_path, false);

        // 出品した商品一覧の確認
        $responseSell = $this->actingAs($me)
            ->get('/mypage?page=sell')
            ->assertOk();
        // 出品した商品が表示されている事を確認
        foreach ($myProducts as $myProduct) {
            $responseSell->assertSeeText($myProduct->name);
        }
        // 上記以外が表示されていない事を確認
        foreach ($otherProducts as $otherProduct) {
            $responseSell->assertDontSeeText($otherProduct->name);
        }
        foreach ($buyProducts as $buyProduct) {
            $responseSell->assertDontSeeText($buyProduct->name);
        }

        // 購入した商品一覧の確認
        $responseBuy = $this->actingAs($me)
            ->get('/mypage?page=buy')
            ->assertOk();
        // 購入した商品が表示されている事を確認
        foreach ($buyProducts as $buyProduct) {
            $responseBuy->assertSeeText($buyProduct->name);
        }
        // 上記以外が表示されていない事を確認
        foreach ($myProducts as $myProduct) {
            $responseBuy->assertDontSeeText($myProduct->name);
        }
        foreach ($otherProducts as $otherProduct) {
            $responseBuy->assertDontSeeText($otherProduct->name);
        }
    }
}
