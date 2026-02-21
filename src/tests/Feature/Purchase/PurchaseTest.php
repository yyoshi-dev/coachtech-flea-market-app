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
use Mockery;
use Stripe\StripeClient;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $seller;
    private Product $product;

    private int $konbiniPaymentMethodId;
    private int $cardPaymentMethodId;

    // =========================
    // 共通処理の関数化
    // =========================

    /**
     * 「共通テストデータ (購入者、出品者、商品)を準備する
     */
    private function prepareUsersAndProduct(): void
    {
        // ユーザーを作成
        $this->buyer = User::factory()->create();
        $this->seller = User::factory()->create();

        // 商品データの作成
        $this->product = Product::factory()->create([
            'user_id' => $this->seller->id,
        ]);

        // 商品カテゴリの紐付け
        $categoryId = ProductCategory::where('name', 'ファッション')->value('id');
        $this->assertNotNull($categoryId);
        $this->product->productCategories()->attach($categoryId);
    }

    /**
     * 支払い方法を準備する
     */
    private function preparePaymentMethodIds(): void
    {
        $this->konbiniPaymentMethodId = PaymentMethod::where('stripe_type', 'konbini')->value('id');
        $this->cardPaymentMethodId = PaymentMethod::where('stripe_type', 'card')->value('id');
    }

    /**
     * Stripeモックを作成する
     */
    private function mockStripeCheckoutSessionCreate(string $url = 'https://stripe.test/checkout-session'): void
    {
        // StripeClientのモックを作成
        $stripeMock = Mockery::mock(StripeClient::class);

        // session->create()を受けるモックを定義
        $sessionsService = Mockery::mock();
        $sessionsService->shouldReceive('create')
            ->once()
            ->andReturn((object)[
                'id' => 'cs_test_123',
                'url' => $url,
            ]);

        // checkout serviceがsessionsプロパティを持っている形を用意
        $checkoutService = new class($sessionsService) {
            public $sessions;
            public function __construct($sessionsService)
            {
                $this->sessions = $sessionsService;
            }
        };

        // Stripe SDKは$stripe->checkoutアクセス時に内部でgetService('checkout)を呼ぶ為、そこを定義
        $stripeMock->shouldReceive('getService')
            ->with('checkout')
            ->once()
            ->andReturn($checkoutService);

        // コンテナにモックを登録
        $this->app->instance(StripeClient::class, $stripeMock);
    }

    /**
     * コンビニ支払いによる購入処理を完了する (ログイン、商品購入画面の表示、購入処理の実行)
     */
    private function completePurchaseFlowByKonbini(User $buyer, Product $product): void
    {
        // ログインして、購入画面を開く
        $this->actingAs($buyer)
            ->get("/purchase/{$product->id}")
            ->assertOk();

        // セッションから住所を取得する
        $address = session('purchase.address');
        $this->assertNotNull($address);

        // フォームリクエスト用のdelivery_addressを作成する
        $deliveryAddress = serialize($address);

        // 購入処理を実行し、トップページにリダイレクトすることを確認する
        $this->actingAs($buyer)
            ->post("/purchase/{$product->id}", [
                'delivery_address' => $deliveryAddress,
                'payment_method_id' => $this->konbiniPaymentMethodId,
            ])
            ->assertRedirect('/');
    }

    /**
     * カード支払いによる購入処理を完了する (ログイン、商品購入画面の表示、購入処理の実行)
     */
    private function completePurchaseFlowByCard(User $buyer, Product $product): void
    {
        // ログインして、購入画面を開く
        $this->actingAs($buyer)
            ->get("/purchase/{$product->id}")
            ->assertOk();

        // セッションから住所を取得する
        $address = session('purchase.address');
        $this->assertNotNull($address);

        // フォームリクエスト用のdelivery_addressを作成する
        $deliveryAddress = serialize($address);

        // Stripeモックを作成
        $this->mockStripeCheckoutSessionCreate();

        // 購入処理を実行し、stripe決済画面へ遷移する事を確認する
        $this->actingAs($buyer)
            ->post("/purchase/{$product->id}", [
                'delivery_address' => $deliveryAddress,
                'payment_method_id' => $this->cardPaymentMethodId,
            ])
            ->assertRedirect('https://stripe.test/checkout-session');

        // stripe決済実行後のsuccess_urlにアクセス
        $successResponse = $this->actingAs($buyer)
            ->get("/purchase/success/{$product->id}");

        // stripe決済完了後にトップページにリダイレクトする事を確認
        $successResponse->assertRedirect('/');
    }

    /**
     * 共通処理セットアップ処理を作成
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 商品状態、商品カテゴリ、支払い方法の投入
        $this->seed(ProductConditionSeeder::class);
        $this->seed(ProductCategorySeeder::class);
        $this->seed(PaymentMethodSeeder::class);

        // 共通テストデータ (購入者、出品者、商品)の準備
        $this->prepareUsersAndProduct();

        // 支払い方法の準備
        $this->preparePaymentMethodIds();
        $this->assertNotNull($this->konbiniPaymentMethodId);
        $this->assertNotNull($this->cardPaymentMethodId);
    }

    // Mockeryの設定をリセット (テスト終了後のクリーンアップ処理)
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 「購入する」ボタンを押下すると購入が完了する
     * コンビニ支払いの場合
     */
    public function test_user_can_purchase_product_by_konbini()
    {
        // 購入前のOrderが0件である事を確認
        $this->assertDatabaseCount('orders', 0);

        // ログインして、購入画面を開き、購入処理を実行する
        $this->completePurchaseFlowByKonbini($this->buyer, $this->product);

        // 購入後のOrderが1件である事を確認
        $this->assertDatabaseCount('orders', 1);

        // オーダーが正しく作成されている事を確認
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'payment_method_id' => $this->konbiniPaymentMethodId,
        ]);

        // 商品が購入済みになっているかを確認
        $this->assertNotNull($this->product->fresh()->sold_at);

        // トップページにリダイレクトされた時に、セッションがクリアされている事を確認
        $this->actingAs($this->buyer)
            ->get('/')
            ->assertSessionMissing('purchase.address')
            ->assertSessionMissing('purchase.product_id')
            ->assertSessionMissing('purchase.payment_method_id');
    }

    /**
     * 「購入する」ボタンを押下すると購入が完了する
     * カード支払いの場合
     */
    public function test_user_can_purchase_product_by_card()
    {
        // 購入前のOrderが0件である事を確認
        $this->assertDatabaseCount('orders', 0);

        // ログインして、購入画面を開き、購入処理を実行し、stripe決済を行う
        $this->completePurchaseFlowByCard($this->buyer, $this->product);

        // stripe決済完了後のOrderが1件である事を確認
        $this->assertDatabaseCount('orders', 1);

        // オーダーが正しく作成されている事を確認
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'payment_method_id' => $this->cardPaymentMethodId,
        ]);

        // 商品が購入済みになっているかを確認
        $this->assertNotNull($this->product->fresh()->sold_at);

        // トップページにリダイレクトされた時に、セッションがクリアされている事を確認
        $this->actingAs($this->buyer)
            ->get('/')
            ->assertSessionMissing('purchase.address')
            ->assertSessionMissing('purchase.product_id')
            ->assertSessionMissing('purchase.payment_method_id');
    }

    /**
     * 購入した商品は商品一覧画面にて「sold」と表示される
     * コンビニ支払いの場合
     */
    public function test_purchased_product_is_shown_as_sold_in_items_list_by_konbini()
    {
        // 商品が購入前である事を確認
        $this->assertNull($this->product->sold_at);

        // ログインして、購入画面を開き、購入処理を実行する
        $this->completePurchaseFlowByKonbini($this->buyer, $this->product);

        // 商品が購入済みになっているかを確認
        $this->assertNotNull($this->product->fresh()->sold_at);

        // トップページにアクセス
        $responseTopPage = $this->actingAs($this->buyer)
            ->get('/')
            ->assertOk();

        // 購入した商品が表示される事を確認
        $responseTopPage->assertSeeText($this->product->name);

        // 購入済み商品に「Sold」ラベルが表示される事を確認
        // 1件の「Sold」ラベルが表示されている事を確認
        $html = $responseTopPage->getContent();
        $this->assertSame(1, substr_count($html, 'data-testid="sold-badge"'));

        // 商品->soldの順に出ている事を確認
        $responseTopPage->assertSeeInOrder([
            $this->product->name,
            'data-testid="sold-badge"',
        ], false);
    }

    /**
     * 購入した商品は商品一覧画面にて「sold」と表示される
     * カード支払いの場合
     */
    public function test_purchased_product_is_shown_as_sold_in_items_list_by_card()
    {
        // 商品が購入前である事を確認
        $this->assertNull($this->product->sold_at);

        // ログインして、購入画面を開き、購入処理を実行し、stripe決済を行う
        $this->completePurchaseFlowByCard($this->buyer, $this->product);

        // 商品が購入済みになっているかを確認
        $this->assertNotNull($this->product->fresh()->sold_at);

        // トップページにアクセス
        $responseTopPage = $this->actingAs($this->buyer)
            ->get('/')
            ->assertOk();

        // 購入した商品が表示される事を確認
        $responseTopPage->assertSeeText($this->product->name);

        // 購入済み商品に「Sold」ラベルが表示される事を確認
        // 1件の「Sold」ラベルが表示されている事を確認
        $html = $responseTopPage->getContent();
        $this->assertSame(1, substr_count($html, 'data-testid="sold-badge"'));

        // 商品->soldの順に出ている事を確認
        $responseTopPage->assertSeeInOrder([
            $this->product->name,
            'data-testid="sold-badge"',
        ], false);
    }

    /**
     * 「プロフィール/購入した商品一覧」に追加されている
     * コンビニ支払いの場合
     */
    public function test_purchased_product_is_added_to_profile_purchased_items_list_by_konbini()
    {
        // 商品が購入前である事を確認
        $this->assertNull($this->product->sold_at);

        // ログインして、購入画面を開き、購入処理を実行する
        $this->completePurchaseFlowByKonbini($this->buyer, $this->product);

        // 商品が購入済みになっているかを確認
        $this->assertNotNull($this->product->fresh()->sold_at);

        // プロフィール画面の購入した商品一覧を表示
        $responseProfile = $this->actingAs($this->buyer)
            ->get('/mypage?page=buy')
            ->assertOk();

        // 購入した商品が表示される事を確認
        $responseProfile->assertSeeText($this->product->name);

        // ビューに渡された購入リストの件数が1件のみである事を確認
        $productId = $this->product->id;

        $responseProfile->assertViewHas('products', function ($products) use ($productId) {
            return $products->count() === 1 && $products->first()->id === $productId;
        });
    }

    /**
     * 「プロフィール/購入した商品一覧」に追加されている
     * カード支払いの場合
     */
    public function test_purchased_product_is_added_to_profile_purchased_items_list_by_card()
    {
        // 商品が購入前である事を確認
        $this->assertNull($this->product->sold_at);

        // ログインして、購入画面を開き、購入処理を実行し、stripe決済を行う
        $this->completePurchaseFlowByCard($this->buyer, $this->product);

        // 商品が購入済みになっているかを確認
        $this->assertNotNull($this->product->fresh()->sold_at);

        // プロフィール画面の購入した商品一覧を表示
        $responseProfile = $this->actingAs($this->buyer)
            ->get('/mypage?page=buy')
            ->assertOk();

        // 購入した商品が表示される事を確認
        $responseProfile->assertSeeText($this->product->name);

        // ビューに渡された購入リストの件数が1件のみである事を確認
        $productId = $this->product->id;

        $responseProfile->assertViewHas('products', function ($products) use ($productId) {
            return $products->count() === 1 && $products->first()->id === $productId;
        });
    }
}
