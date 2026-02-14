<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    // テスト実行毎にデータベースをリセット
    use RefreshDatabase;

    /**
     * 共通の登録リクエスト送信メソッド
     * テストコードの重複を避ける為のプライベート関数
     */
    private function postLogin(array $overrides = []) {
        $default = [
            'email' => 'test@example.com',
            'password' => 'test1234',
        ];

        return $this->post('/login', array_merge($default, $overrides));
    }

    /**
     * メールアドレスが入力されていない場合、バリデーションメッセージが表示される
     */
    public function testEmailIsRequired() {
        // ログインページが開ける事を確認
        $this->get('/login')->assertStatus(200);

        // メールアドレスを入力せずに他の必要項目を入力して送信
        $response = $this->postLogin(['email' => '']);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
    }

    /**
     * パスワードが入力されていない場合、バリデーションメッセージが表示される
     */
    public function testPasswordIsRequired() {
        // ログインページが開ける事を確認
        $this->get('/login')->assertStatus(200);

        // パスワードを入力せずに他の必要項目を入力して送信
        $response = $this->postLogin(['password' => '']);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
    }

    /**
     * 入力情報が間違っている場合、バリデーションメッセージが表示される
     * データベースにデータがない場合の検証
     */
    public function testLoginFailsWhenUserDoesNotExist() {
        // ログインページが開ける事を確認
        $this->get('/login')->assertStatus(200);

        // 登録されていない情報を入力して送信
        $response = $this->postLogin([
            'email' => 'nonexistent@example.com',
            'password' => 'test1234'
        ]);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors([
            'password' => 'ログイン情報が登録されていません'
        ]);

        // ログインしていない事を確認
        $this->assertGuest();
    }

    /**
     * 入力情報が間違っている場合、バリデーションメッセージが表示される
     * ユーザーは登録されているが、パスワードが間違っている場合の検証
     */
    public function testLoginFailsWithWrongPassword() {
        // ユーザーを登録
        User::factory()->create([
            'name' => '検証ユーザー',
            'email' => 'registered@example.com',
            'password' => Hash::make('test1234'),
        ]);

        // ログインページが開ける事を確認
        $this->get('/login')->assertStatus(200);

        // 誤ったパスワードを入力して送信
        $response = $this->postLogin([
            'email' => 'registered@example.com',
            'password' => 'wrong-password'
        ]);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors([
            'password' => 'ログイン情報が登録されていません'
        ]);

        // ログインしていない事を確認
        $this->assertGuest();
    }

    /**
     * 正しい情報が入力された場合、ログイン処理が実行される
     */
    public function testUserCanLogin() {
        // ユーザーを登録
        $user = User::factory()->create([
            'name' => '検証ユーザー',
            'email' => 'success@example.com',
            'password' => Hash::make('test1234'),
        ]);

        // ログインページが開ける事を確認
        $this->get('/login')->assertStatus(200);

        // 全ての必須項目を入力して送信
        $response = $this->postLogin([
            'email' => 'success@example.com',
            'password' => 'test1234'
        ]);

        // ログイン処理が実行される
        $this->assertAuthenticatedAs($user);
    }
}
