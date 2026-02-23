<?php

namespace Tests\Feature\Items;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\ProductCategory;
use App\Models\ProductCondition;
use App\Models\User;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\ProductConditionSeeder;

class SellTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 共通処理セットアップ処理を作成
     */
    protected function setUp(): void
    {
        parent::setUp();

        // ストレージを擬装する
        Storage::fake('public');
    }

    /**
     * 商品出品画面にて必要な情報が保存できること (カテゴリ、商品の状態、商品名、ブランド名、商品の説明、販売価格)
     */
    public function test_user_can_sell_product_with_required_information()
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 商品状態データ、商品カテゴリデータの作成
        $this->seed(ProductConditionSeeder::class);
        $this->seed(ProductCategorySeeder::class);
        $categoryIds = ProductCategory::whereIn('name', ['ファッション', 'メンズ'])
            ->pluck('id')
            ->all();

        $conditionId = ProductCondition::where('name', '良好')->value('id');
        $this->assertNotNull($conditionId);

        // ダミーの画像ファイルを作成する
        $productImage = UploadedFile::fake()->create('test.jpeg', 100);

        // 商品情報を定義
        $productData = [
            'name' => 'TEST_PRODUCT',
            'brand_name' => 'TEST_BRAND',
            'description' => 'THIS_PRODUCT_IS_TEST_PRODUCT',
            'price' => 10000,
            'product_condition_id' => $conditionId,
            'product_image' => $productImage,
            'product_category_ids' => $categoryIds,
        ];

        // 出品前の商品が0件である事を確認
        $this->assertDatabaseCount('products', 0);

        // ログインして、商品出品画面を開く
        $this->actingAs($user)->get('/sell')->assertOk();

        // 商品を出品する
        $this->actingAs($user)
            ->post('/sell', $productData)
            ->assertRedirect('/mypage');

        // 出品後の商品が1件である事を確認
        $this->assertDatabaseCount('products', 1);

        // 出品商品が正しく作成されている事を確認
        $this->assertDatabaseHas('products', [
            'user_id' => $user->id,
            'name' => 'TEST_PRODUCT',
            'brand_name' => 'TEST_BRAND',
            'description' => 'THIS_PRODUCT_IS_TEST_PRODUCT',
            'price' => 10000,
            'product_condition_id' => $conditionId,
        ]);

        // 保存された商品を取得
        $product = Product::firstOrFail();

        // 画像が保存されている事を確認
        $this->assertTrue(
            Storage::disk('public')->exists($product->product_image_path)
        );

        // 商品カテゴリとのリレーションが正しく作成されている事を確認
        foreach ($categoryIds as $categoryId) {
            $this->assertDatabaseHas('product_category_relations', [
                'product_id' => $product->id,
                'product_category_id' => $categoryId,
            ]);
        }
    }

    /**
     * 共通の出品リクエスト送信メソッド (下記のオプションテスト実施用)
     * テストコードの重複を避ける為のプライベート関数
     */
    private function postSell(array $overrides = []) {
        $user = User::factory()->create();

        // 商品状態データ、商品カテゴリデータの作成
        $this->seed(ProductConditionSeeder::class);
        $this->seed(ProductCategorySeeder::class);

        $categoryIds = ProductCategory::whereIn('name', ['ファッション', 'メンズ'])
            ->pluck('id')
            ->all();

        $conditionId = ProductCondition::where('name', '良好')->value('id');
        $this->assertNotNull($conditionId);

        // ダミーの画像ファイルを作成する
        $productImage = UploadedFile::fake()->create('test.jpeg', 100);

        // 商品情報を定義
        $default = [
            'name' => 'TEST_PRODUCT',
            'brand_name' => 'TEST_BRAND',
            'description' => 'THIS_PRODUCT_IS_TEST_PRODUCT',
            'price' => 10000,
            'product_condition_id' => $conditionId,
            'product_image' => $productImage,
            'product_category_ids' => $categoryIds,
        ];

        // ログインして商品を出品する
        return $this->actingAs($user)->post('/sell', array_merge($default, $overrides));
    }

    /**
     * オプション: 商品名が入力されていない場合、バリデーションエラーとなる
     */
    public function test_product_name_is_required()
    {
        // 商品名無しで出品し、バリデーションエラーとなる事を確認
        $response = $this->postSell(['name' => '']);
        $response->assertSessionHasErrors('name');

        $this->assertTrue(
            collect(session('errors')->get('name'))->contains('商品名を入力してください')
        );
    }

    /**
     * オプション: 商品説明が入力されていない場合、バリデーションエラーとなる
     */
    public function test_description_is_required()
    {
        // 商品説明無しで出品し、バリデーションエラーとなる事を確認
        $response = $this->postSell(['description' => '']);
        $response->assertSessionHasErrors('description');

        $this->assertTrue(
            collect(session('errors')->get('description'))->contains('商品説明を入力してください')
        );
    }

    /**
     * オプション: 商品説明が256文字以上の場合、バリデーションエラーとなる
     */
    public function test_description_max_length_is_limited()
    {
        // 商品説明を256文字以上で出品し、バリデーションエラーとなる事を確認
        $tooLongDescription = str_repeat('a', 256);
        $response = $this->postSell(['description' => $tooLongDescription]);
        $response->assertSessionHasErrors('description');

        $this->assertTrue(
            collect(session('errors')->get('description'))->contains('商品説明は255文字以下で入力してください')
        );
    }

    /**
     * オプション: 商品画像がアップロードされていない場合、バリデーションエラーとなる
     */
    public function test_product_image_is_required()
    {
        // 商品画像無しで出品し、バリデーションエラーとなる事を確認
        $response = $this->postSell(['product_image' => null]);
        $response->assertSessionHasErrors('product_image');

        $this->assertTrue(
            collect(session('errors')->get('product_image'))->contains('商品画像をアップロードしてください')
        );
    }
    /**
     * オプション: プロフィール画像の拡張子が異なる場合、バリデーションエラーとなる
     */
    public function test_product_image_must_be_valid_format()
    {
        // ダミーの画像ファイルを作成する
        $invalidImage = UploadedFile::fake()->create('dummy.svg', 100);

        // 異なる画像拡張子で出品し、バリデーションエラーとなる事を確認
        $response = $this->postSell(['product_image' => $invalidImage]);
        $response->assertSessionHasErrors('product_image');

        $this->assertTrue(
            collect(session('errors')->get('product_image'))
                ->contains('商品画像の拡張子は.jpegもしくは.pngでアップロードしてください')
        );
    }

    /**
     * オプション: 商品カテゴリーが選択されていない場合、バリデーションエラーとなる
     */
    public function test_product_category_is_required()
    {
        // 商品カテゴリー無しで出品し、バリデーションエラーとなる事を確認
        $response = $this->postSell(['product_category_ids' => []]);
        $response->assertSessionHasErrors('product_category_ids');

        $this->assertTrue(
            collect(session('errors')->get('product_category_ids'))->contains('商品のカテゴリーを選択してください')
        );
    }

    /**
     * オプション: 商品状態が選択されていない場合、バリデーションエラーとなる
     */
    public function test_product_condition_is_required()
    {
        // 商品状態無しで出品し、バリデーションエラーとなる事を確認
        $response = $this->postSell(['product_condition_id' => '']);
        $response->assertSessionHasErrors('product_condition_id');

        $this->assertTrue(
            collect(session('errors')->get('product_condition_id'))->contains('商品の状態を選択してください')
        );
    }

    /**
     * オプション: 商品価格が入力されていない場合、バリデーションエラーとなる
     */
    public function test_price_is_required()
    {
        // 価格無しで出品し、バリデーションエラーとなる事を確認
        $response = $this->postSell(['price' => '']);
        $response->assertSessionHasErrors('price');

        $this->assertTrue(
            collect(session('errors')->get('price'))->contains('商品価格を入力してください')
        );
    }

    /**
     * オプション: 商品価格が数値型で入力されていない場合、バリデーションエラーとなる
     */
    public function test_price_must_be_numeric_format()
    {
        // 価格無しで出品し、バリデーションエラーとなる事を確認
        $response = $this->postSell(['price' => 'aaa']);
        $response->assertSessionHasErrors('price');

        $this->assertTrue(
            collect(session('errors')->get('price'))->contains('商品価格は数値型で入力してください')
        );
    }
    /**
     * オプション: 商品価格が0円より小さい場合、バリデーションエラーとなる
     */
    public function test_price_must_be_larger_than_zero()
    {
        // 価格を0円より小さい値で出品し、バリデーションエラーとなる事を確認
        $response = $this->postSell(['price' => -100]);
        $response->assertSessionHasErrors('price');

        $this->assertTrue(
            collect(session('errors')->get('price'))->contains('商品価格は0円以上で入力してください')
        );
    }
}
