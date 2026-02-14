<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログアウトができる
     */
    public function testUserCanLogout()
    {
        // ユーザーを登録
        $user = User::factory()->create([
            'name' => '検証ユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('test1234'),
        ]);

        // ログインする
        $this->actingAs($user);

        // ログアウトする
        $response = $this->post('/logout');

        // ログアウトされている事を確認
        $this->assertGuest();
    }
}
