<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use App\Models\User;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録後、認証メールが送信される
     */
    public function test_verification_email_is_sent()
    {
        // メールの送信を擬装する
        Notification::fake();

        // 会員登録処理を実行する
        $userData = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
        ];
        $this->post('/register', $userData);

        // 指定したメールアドレス宛に認証メールが送信されたかを確認
        $user = User::firstWhere('email', 'test@example.com');
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
     */
    public function test_redirect_to_email_verification_site() {
        // 未認証ユーザーとしてログイン
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        $this->actingAs($user);

        // メール認証誘導画面を表示出来る事を確認
        $response = $this->get('/email/verify');
        $response->assertStatus(200);

        // 「認証はこちらから」ボタンを押すとメール認証サイト (今回はMailHogサイト)に遷移する事を確認
        $response = $this->get('/email/verify/mailhog');
        $response->assertRedirect(config('services.mailhog.url'));
    }

    /**
     * メール認証サイトのメール認証を完了すると、プロフィール設定画面に遷移する
     */
    public function test_email_verification_redirects_to_profile_setting() {
        // 未認証ユーザーとしてログイン
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 認証用の署名付きURLを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),   // 60分間有効なURL
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification())    //メールアドレスをハッシュ化
            ]
        );

        // メール認証を実施
        $response = $this->actingAs($user)->get($verificationUrl);

        // プロフィール設定画面に遷移する事を確認
        $response->assertRedirect('/mypage/profile');

        // プロフィール設定画面が表示される事を確認
        $this->get('/mypage/profile')->assertStatus(200);

        // email_verified_atが更新されている事を確認
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
