<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    // テスト実行毎にデータベースをリセット
    use RefreshDatabase;

    /**
     * 共通の登録リクエスト送信メソッド
     * テストコードの重複を避ける為のプライベート関数
     */
    private function postRegistration(array $overrides = []) {
        $default = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
        ];

        return $this->post('/register', array_merge($default, $overrides));
    }

    /**
     * 名前が入力されていない場合、バリデーションメッセージが表示される
     */
    public function testNameIsRequired() {
        // 会員登録ページが開ける事を確認
        $this->get('/register')->assertStatus(200);

        // 名前を入力せずに他の必要項目を入力して送信
        $response = $this->postRegistration(['name' => '']);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください'
        ]);

        // DBにユーザーが登録されていない事を確認
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * メールアドレスが入力されていない場合、バリデーションメッセージが表示される
     */
    public function testEmailIsRequired() {
        // 会員登録ページが開ける事を確認
        $this->get('/register')->assertStatus(200);

        // メールアドレスを入力せずに他の必要項目を入力して送信
        $response = $this->postRegistration(['email' => '']);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);

        // DBにユーザーが登録されていない事を確認
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * パスワードが入力されていない場合、バリデーションメッセージが表示される
     */
    public function testPasswordIsRequired() {
        // 会員登録ページが開ける事を確認
        $this->get('/register')->assertStatus(200);

        // パスワードを入力せずに他の必要項目を入力して送信
        $response = $this->postRegistration(['password' => '']);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);

        // DBにユーザーが登録されていない事を確認
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * パスワードが7文字以下の場合、バリデーションメッセージが表示される
     */
    public function testPasswordIsTooShort() {
        // 会員登録ページが開ける事を確認
        $this->get('/register')->assertStatus(200);

        // パスワードを7文字以下で入力して送信
        $response = $this->postRegistration([
            'password' => '1234567',
            'password_confirmation' => '1234567'
        ]);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください'
        ]);

        // DBにユーザーが登録されていない事を確認
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * パスワードが確認用パスワードと一致しない場合、バリデーションメッセージが表示される
     */
    public function testPasswordConfirmationDoesNotMatch() {
        // 会員登録ページが開ける事を確認
        $this->get('/register')->assertStatus(200);

        // パスワードと確認用パスワードを一致させずに送信
        $response = $this->postRegistration([
            'password' => 'test1234',
            'password_confirmation' => 'test5678'
        ]);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors([
            'password_confirmation' => 'パスワードと一致しません'
        ]);

        // DBにユーザーが登録されていない事を確認
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * 全ての項目が入力されている場合、会員情報が登録され、メール認証誘導画面に遷移される
     */
    public function testUserIsRegisteredAndRedirectedToEmailVerification() {
        // 会員登録ページが開ける事を確認
        $this->get('/register')->assertStatus(200);

        // 全ての項目を入力して送信
        $userData = [
            'name' => 'テスト太郎',
            'email' => 'success@example.com',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
        ];
        $response = $this->postRegistration($userData);

        // DBに保存されているかを確認
        $this->assertDatabaseHas('users', [
            'email' => 'success@example.com'
        ]);

        // メール認証誘導画面に遷移される事を確認
        $response->assertRedirect('/email/verify');
    }
}
