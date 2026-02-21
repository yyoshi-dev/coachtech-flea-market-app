<?php

namespace Tests\Feature\Purchase;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PaymentMethod;
use App\Models\User;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\ProductConditionSeeder;

class SelectPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 小計画面で変更が反映される
     */
    public function test_selected_payment_method_is_reflected_in_subtotal_section()
    {
        // ユーザーを作成
        $user = User::factory()->create();
        $other = User::factory()->create();

        // 商品状態データ、商品カテゴリデータ、支払い方法データの作成
        $this->seed(ProductConditionSeeder::class);
        $this->seed(ProductCategorySeeder::class);
        $this->seed(PaymentMethodSeeder::class);

        // 商品データの作成
        $product = Product::factory()->create([
            'user_id' => $other->id,
        ]);

        // 商品カテゴリの紐付け
        $categoryId = ProductCategory::where('name', 'ファッション')->value('id');
        $this->assertNotNull($categoryId);
        $product->productCategories()->attach($categoryId);

        // ログインして、購入画面を開く
        $this->actingAs($user)
            ->get("/purchase/{$product->id}")
            ->assertOk();

        // 支払い方法を準備する
        $methodKonbini = PaymentMethod::where('stripe_type', 'konbini')->first();
        $methodCard = PaymentMethod::where('stripe_type', 'card')->first();
        $this->assertNotNull($methodKonbini);
        $this->assertNotNull($methodCard);

        // いずれかの支払い方法を選択する
        $this->actingAs($user)
            ->post("/purchase/payment/{$product->id}", [
                'payment_method_id' => $methodKonbini->id,
            ])
            ->assertRedirect("/purchase/{$product->id}");

        // 選択した支払方法が小計画面に表示されている事を確認
        $this->actingAs($user)
            ->get("/purchase/{$product->id}")
            ->assertOk()
            ->assertSessionHas('purchase.payment_method_id', $methodKonbini->id)
            ->assertSeeInOrder([
                'data-testid="subtotal-payment-method"',
                $methodKonbini->name,
            ], false);

        // 別の支払い方法に変更する
        $this->actingAs($user)
            ->post("/purchase/payment/{$product->id}", [
                'payment_method_id' => $methodCard->id,
            ])
            ->assertRedirect("/purchase/{$product->id}");

        // 小計画面の表示が変更した支払方法に変わっている事を確認
        $this->actingAs($user)
            ->get("/purchase/{$product->id}")
            ->assertOk()
            ->assertSessionHas('purchase.payment_method_id', $methodCard->id)
            ->assertSeeInOrder([
                'data-testid="subtotal-payment-method"',
                $methodCard->name,
            ], false);
    }
}
