<?php

namespace Tests\Feature\Mypage;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;

class EditProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 変更項目が初期値として過去設定されていること (プロフィール画像、ユーザー名、郵便番号、住所)
     */
    public function test_profile_edit_form_displays_saved_user_values_as_defaults()
    {
        // ユーザーを作成
        $user = User::factory()->create([
            'name' => 'TEST_USER',
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市1-1-1',
            'profile_image_path' => 'profiles/dummy_profile.jpeg',
        ]);

        // ログインしてプロフィールページを開く
        $response = $this->actingAs($user)
            ->get('/mypage/profile')
            ->assertOk();

        // ユーザー名が表示されている事を確認
        $response->assertSeeInOrder([
            'id="name"',
            'value="' . e($user->name) . '"',
        ], false);

        // 郵便番号が表示されている事を確認
        $response->assertSeeInOrder([
            'id="postal_code"',
            'value="' . e($user->postal_code) . '"',
        ], false);

        // 住所が表示されている事を確認
        $response->assertSeeInOrder([
            'id="address"',
            'value="' . e($user->address) . '"',
        ], false);

        // プロフィール画像が表示されている事を確認
        $response->assertSeeInOrder([
            '<img',
            'src="' . asset('storage/' . $user->profile_image_path) . '"',
        ], false);
    }

    /**
     * オプション: プロフィール画像の拡張子が異なる場合、バリデーションエラーとなる
     */
    public function test_profile_image_must_be_valid_format_on_profile_update()
    {
        $user = User::factory()->create();

        // ダミーの画像ファイルを作成する
        $invalidImage = UploadedFile::fake()->create('dummy.svg', 100);

        // 新しいプロフィールを定義
        $newProfile = [
            'name' => 'TEST_USER',
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市1-1-1',
            'building' => 'テストマンション101',
            'profile_image' => $invalidImage,
        ];

        // ログインして、異なる画像拡張子でプロフィールを変更し、バリデーションエラーとなる事を確認
        $this->actingAs($user)
            ->post('/mypage/profile', $newProfile)
            ->assertSessionHasErrors('profile_image');

        $this->assertTrue(
            collect(session('errors')->get('profile_image'))
                ->contains('プロフィール画像の拡張子は.jpegもしくは.pngでアップロードしてください')
        );
    }

    /**
     * オプション: 名前が入力されていない場合、バリデーションエラーとなる
     */
    public function test_user_name_is_required_on_profile_update()
    {
        $user = User::factory()->create();

        // 新しいプロフィールを定義
        $newProfile = [
            'name' => '',
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市1-1-1',
            'building' => 'テストマンション101',
        ];

        // ログインして、名前無しでプロフィールを変更し、バリデーションエラーとなる事を確認
        $this->actingAs($user)
            ->post('/mypage/profile', $newProfile)
            ->assertSessionHasErrors('name');

        $this->assertTrue(
            collect(session('errors')->get('name'))->contains('お名前を入力してください')
        );
    }

    /**
     * オプション: 名前が20文字以上の場合、バリデーションエラーとなる
     */
    public function test_user_name_max_length_validation_error_is_displayed_on_profile_update()
    {
        $user = User::factory()->create();

        // 新しいプロフィールを定義
        $tooLongName = str_repeat('a', 21);
        $newProfile = [
            'name' => $tooLongName,
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市1-1-1',
            'building' => 'テストマンション101',
        ];

        // ログインして、名前無しでプロフィールを変更し、バリデーションエラーとなる事を確認
        $this->actingAs($user)
            ->post('/mypage/profile', $newProfile)
            ->assertSessionHasErrors('name');

        $this->assertTrue(
            collect(session('errors')->get('name'))->contains('お名前は20文字以下で入力してください')
        );
    }

    /**
     * オプション: 郵便番号が入力されていない場合、バリデーションエラーとなる
     */
    public function test_postal_code_is_required_on_profile_update()
    {
        $user = User::factory()->create();

        // 新しいプロフィールを定義
        $newProfile = [
            'name' => 'TEST_USER',
            'postal_code' => '',
            'address' => 'テスト県テスト市1-1-1',
            'building' => 'テストマンション101',
        ];

        // ログインして、郵便番号無しでプロフィールを変更し、バリデーションエラーとなる事を確認
        $this->actingAs($user)
            ->post('/mypage/profile', $newProfile)
            ->assertSessionHasErrors('postal_code');

        $this->assertTrue(
            collect(session('errors')->get('postal_code'))->contains('郵便番号を入力してください')
        );
    }

    /**
     * オプション: 郵便番号がハイフンありの8文字でない場合、バリデーションエラーとなる
     */
    #[DataProvider('invalid_postal_code_provider')]
    public function test_postal_code_must_be_valid_format_on_profile_update(array $payload, string $field, string $message)
    {
        $user = User::factory()->create();

        $base = [
            'name' => 'TEST_USER',
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市1-1-1',
            'building' => 'テストマンション101',
        ];

        $payload = array_merge($base, $payload);

        // ログインして、郵便番号をハイフンありの8文字以外としプロフィールを変更し、バリデーションエラーとなる事を確認
        $this->actingAs($user)
            ->post('/mypage/profile', $payload)
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
                ],
                'postal_code',
                '郵便番号はハイフンありの8文字で入力してください',
            ],
            'invalid hyphen position' => [
                [
                    'postal_code' => '12-34567',
                ],
                'postal_code',
                '郵便番号はハイフンありの8文字で入力してください',
            ],
            'non numeric characters' => [
                [
                    'postal_code' => 'abc-defg',
                ],
                'postal_code',
                '郵便番号はハイフンありの8文字で入力してください',
            ],
        ];
    }

    /**
     * オプション: 住所が入力されていない場合、バリデーションエラーとなる
     */
    public function test_address_is_required_on_profile_update()
    {
        $user = User::factory()->create();

        // 新しいプロフィールを定義
        $newProfile = [
            'name' => 'TEST_USER',
            'postal_code' => '123-4567',
            'address' => '',
            'building' => 'テストマンション101',
        ];

        // ログインして、住所無しでプロフィールを変更し、バリデーションエラーとなる事を確認
        $this->actingAs($user)
            ->post('/mypage/profile', $newProfile)
            ->assertSessionHasErrors('address');

        $this->assertTrue(
            collect(session('errors')->get('address'))->contains('住所を入力してください')
        );
    }
}
