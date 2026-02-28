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
use PHPUnit\Framework\Attributes\DataProvider;

class EditAddressTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $seller;
    private Product $product;

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
    }

    /**
     * 送付先住所変更画面にて登録した住所が商品購入画面に反映されている
     */
    public function test_delivery_address_registered_on_edit_page_is_reflected_on_purchase_page()
    {
        // 新しい配送先を定義
        $newAddress = [
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市1-1-1',
            'building' => 'テストマンション101',
        ];

        // ログインして、購入画面を開き、ユーザーの住所が初期値として表示されている事を確認
        $this->actingAs($this->buyer)
            ->get("/purchase/{$this->product->id}")
            ->assertOk()
            ->assertSee('data-testid="delivery-address"', false)
            ->assertSee($this->buyer->postal_code, false)
            ->assertSee($this->buyer->address, false)
            ->assertSee($this->buyer->building, false);

        // 配送先変更画面を開く
        $this->actingAs($this->buyer)
            ->get("/purchase/address/{$this->product->id}")
            ->assertOk();

        // 配送先を新たに登録する
        $this->actingAs($this->buyer)
            ->post("/purchase/address/{$this->product->id}", $newAddress)
            ->assertRedirect("/purchase/{$this->product->id}")
            ->assertSessionHas('purchase.address.postal_code', $newAddress['postal_code'])
            ->assertSessionHas('purchase.address.address', $newAddress['address'])
            ->assertSessionHas('purchase.address.building', $newAddress['building']);

        // 商品購入画面に登録した住所が正しく反映されている事を確認
        $response = $this->actingAs($this->buyer)
            ->get("/purchase/{$this->product->id}")
            ->assertOk();
        $response->assertSee('data-testid="delivery-address"', false);
        foreach ($newAddress as $value) {
            if ($value !== null && $value !== '') {
                $response->assertSee($value, false);
            }
}
    }

    /**
     * 購入した商品に送付先住所が紐づいて登録される
     * コンビニ支払いの場合
     */
    public function test_purchased_order_is_created_with_delivery_address_by_konbini()
    {
        // 新しい配送先を定義
        $newAddress = [
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市1-1-1',
            'building' => 'テストマンション101',
        ];

        // ログインして、配送先変更画面を開く
        $this->actingAs($this->buyer)
            ->get("/purchase/address/{$this->product->id}")
            ->assertOk();

        // 配送先を新たに登録する
        $this->actingAs($this->buyer)
            ->post("/purchase/address/{$this->product->id}", $newAddress)
            ->assertRedirect("/purchase/{$this->product->id}")
            ->assertSessionHas('purchase.address.postal_code', $newAddress['postal_code'])
            ->assertSessionHas('purchase.address.address', $newAddress['address'])
            ->assertSessionHas('purchase.address.building', $newAddress['building']);

        // フォームリクエスト用のdelivery_addressを作成する
        $deliveryAddress = serialize($newAddress);

        // 支払い方法の準備
        $selectedMethodId = PaymentMethod::where('stripe_type', 'konbini')->value('id');
        $this->assertNotNull($selectedMethodId);

        // 購入前のOrderが0件である事を確認
        $this->assertDatabaseCount('orders', 0);

        // 購入処理を実行し、トップページにリダイレクトすることを確認
        $this->actingAs($this->buyer)
            ->post("/purchase/{$this->product->id}", [
                'delivery_address' => $deliveryAddress,
                'payment_method_id' => $selectedMethodId,
            ])
            ->assertRedirect('/');

        // 購入後のOrderが1件である事を確認
        $this->assertDatabaseCount('orders', 1);

        // オーダーが新しい配送先で正しく作成されている事を確認
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'payment_method_id' => $selectedMethodId,
            'postal_code' => $newAddress['postal_code'],
            'address' => $newAddress['address'],
            'building' => $newAddress['building'],
        ]);
    }

    /**
     * 購入した商品に送付先住所が紐づいて登録される
     * カード支払いの場合
     */
    public function test_purchased_order_is_created_with_delivery_address_by_card()
    {
        // 新しい配送先を定義
        $newAddress = [
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市1-1-1',
            'building' => 'テストマンション101',
        ];

        // ログインして、配送先変更画面を開く
        $this->actingAs($this->buyer)
            ->get("/purchase/address/{$this->product->id}")
            ->assertOk();

        // 配送先を新たに登録する
        $this->actingAs($this->buyer)
            ->post("/purchase/address/{$this->product->id}", $newAddress)
            ->assertRedirect("/purchase/{$this->product->id}")
            ->assertSessionHas('purchase.address.postal_code', $newAddress['postal_code'])
            ->assertSessionHas('purchase.address.address', $newAddress['address'])
            ->assertSessionHas('purchase.address.building', $newAddress['building']);

        // フォームリクエスト用のdelivery_addressを作成する
        $deliveryAddress = serialize($newAddress);

        // 支払い方法の準備
        $selectedMethodId = PaymentMethod::where('stripe_type', 'card')->value('id');
        $this->assertNotNull($selectedMethodId);

        // StripeClientのモックを作成
        $stripeMock = Mockery::mock(StripeClient::class);

        // session->create()を受けるモックを定義
        $sessionsService = Mockery::mock();
        $sessionId = 'cs_test_123';
        $capturedCreatePayload = null;  // create()に実際に渡された引数を保持し、後続のretrieve()返却値と整合させる為の変数
        $sessionsService->shouldReceive('create')
            ->once()
            // ここがcreate()の引数チェック本体
            ->withArgs(function (array $payload) use (&$capturedCreatePayload, $selectedMethodId) {
                // 引数を保存して、success側(retrieve)の返却値生成に使う
                $capturedCreatePayload = $payload;

                return ($payload['mode'] ?? null) === 'payment'
                    && ($payload['payment_method_types'] ?? null) === ['card']
                    && ($payload['success_url'] ?? '') === url("/purchase/success/{$this->product->id}?session_id={CHECKOUT_SESSION_ID}")
                    && ($payload['cancel_url'] ?? '') === url("/purchase/{$this->product->id}")
                    && (string) ($payload['metadata']['item_id'] ?? '') === (string) $this->product->id
                    && (string) ($payload['metadata']['payment_method_id'] ?? '') === (string) $selectedMethodId;
            })
            ->andReturn((object)[
                'id' => $sessionId,
                'url' => 'https://stripe.test/checkout-session',
            ]);

        // checkout serviceがsessionsプロパティを持っている形を用意
        $checkoutService = new class($sessionsService) {
            public $sessions;
            public function __construct($sessionsService)
            {
                $this->sessions = $sessionsService;
            }
        };

        // stripe success時のretrieve結果を定義
        $sessionsService->shouldReceive('retrieve')
            ->once()
            ->with($sessionId, [])
            // create()で渡された引数を使って返却値を組み立て、create/retrieveの整合を検証する
            ->andReturnUsing(function () use (&$capturedCreatePayload) {
                return (object) [
                    'payment_status' => 'paid',
                    'client_reference_id' => (string) ($capturedCreatePayload['client_reference_id'] ?? ''),
                    'metadata' => (object) [
                        'item_id' => (string) ($capturedCreatePayload['metadata']['item_id'] ?? ''),
                        'payment_method_id' => (string) ($capturedCreatePayload['metadata']['payment_method_id'] ?? ''),
                    ],
                ];
            });

        // Stripe SDKは$stripe->checkoutアクセス時に内部でgetService('checkout')を呼ぶ為、そこを定義
        $stripeMock->shouldReceive('getService')
            ->with('checkout')
            ->twice()
            ->andReturn($checkoutService);

        // コンテナにモックを登録
        $this->app->instance(StripeClient::class, $stripeMock);

        // 購入前のOrderが0件である事を確認
        $this->assertDatabaseCount('orders', 0);

        // 購入処理を実行し、stripe決済画面へ遷移する事を確認する
        $this->actingAs($this->buyer)
            ->post("/purchase/{$this->product->id}", [
                'delivery_address' => $deliveryAddress,
                'payment_method_id' => $selectedMethodId,
            ])
            ->assertRedirect('https://stripe.test/checkout-session')
            ->assertSessionHas('purchase.payment_method_id', $selectedMethodId);

        // stripe決済実行後のsuccess_urlにアクセスし、トップページにリダイレクトする事を確認
        $this->actingAs($this->buyer)
            ->get("/purchase/success/{$this->product->id}?session_id=cs_test_123")
            ->assertRedirect('/');

        // 購入後のOrderが1件である事を確認
        $this->assertDatabaseCount('orders', 1);

        // オーダーが新しい配送先で正しく作成されている事を確認
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'payment_method_id' => $selectedMethodId,
            'postal_code' => $newAddress['postal_code'],
            'address' => $newAddress['address'],
            'building' => $newAddress['building'],
        ]);
    }

    /**
     * オプション: 郵便番号が入力されていない場合、バリデーションエラーとなる
     */
    public function test_postal_code_is_required_on_address_update()
    {
        // 新しい配送先を定義
        $newAddress = [
            'postal_code' => '',
            'address' => 'テスト県テスト市1-1-1',
            'building' => 'テストマンション101',
        ];

        // ログインして、郵便番号無しで配送先を新たに登録し、バリデーションエラーとなる事を確認
        $this->actingAs($this->buyer)
            ->post("/purchase/address/{$this->product->id}", $newAddress)
            ->assertSessionHasErrors('postal_code');

        $this->assertTrue(
            collect(session('errors')->get('postal_code'))->contains('郵便番号を入力してください')
        );
    }

    /**
     * オプション: 郵便番号がハイフンありの8文字でない場合、バリデーションエラーとなる
     */
    #[DataProvider('invalid_postal_code_provider')]
    public function test_postal_code_must_be_valid_format_on_address_update(array $payload, string $field, string $message)
    {
        // ログインして、郵便番号をハイフンありの8文字以外とし配送先を新たに登録し、バリデーションエラーとなる事を確認
        $this->actingAs($this->buyer)
            ->post("/purchase/address/{$this->product->id}", $payload)
            ->assertSessionHasErrors($field);

        $this->assertTrue(
            collect(session('errors')->get($field))->contains($message)
        );
    }

    public static function invalid_postal_code_provider(): array
    {
        return [
            'missing hyphen' => [
                [
                    'postal_code' => '1234567',
                    'address' => 'テスト県テスト市1-1-1',
                    'building' => 'テストマンション101',
                ],
                'postal_code',
                '郵便番号はハイフンありの8文字で入力してください',
            ],
            'invalid hyphen position' => [
                [
                    'postal_code' => '12-34567',
                    'address' => 'テスト県テスト市1-1-1',
                    'building' => 'テストマンション101',
                ],
                'postal_code',
                '郵便番号はハイフンありの8文字で入力してください',
            ],
            'non numeric characters' => [
                [
                    'postal_code' => 'abc-defg',
                    'address' => 'テスト県テスト市1-1-1',
                    'building' => 'テストマンション101',
                ],
                'postal_code',
                '郵便番号はハイフンありの8文字で入力してください',
            ],
        ];
    }

    /**
     * オプション: 住所が入力されていない場合、バリデーションエラーとなる
     */
    public function test_address_is_required_on_address_update()
    {
        // 新しい配送先を定義
        $newAddress = [
            'postal_code' => '123-4567',
            'address' => '',
            'building' => 'テストマンション101',
        ];

        // ログインして、住所無しで配送先を新たに登録し、バリデーションエラーとなる事を確認
        $this->actingAs($this->buyer)
            ->post("/purchase/address/{$this->product->id}", $newAddress)
            ->assertSessionHasErrors('address');

        $this->assertTrue(
            collect(session('errors')->get('address'))->contains('住所を入力してください')
        );
    }
}
