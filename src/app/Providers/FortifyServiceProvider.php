<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Http\Requests\LoginRequest as CustomLoginRequest;

use Laravel\Fortify\Contracts\RegisterResponse as FortifyRegisterResponse;
use App\Responses\Fortify\RegisterResponse as CustomRegisterResponse;

use Laravel\Fortify\Contracts\VerifyEmailResponse as FortifyVerifyEmailResponse;
use App\Responses\Fortify\VerifyEmailResponse as CustomVerifyEmailResponse;

use Laravel\Fortify\Contracts\LoginResponse as FortifyLoginResponse;
use App\Responses\Fortify\LoginResponse as CustomLoginResponse;

use Laravel\Fortify\Contracts\LogoutResponse as FortifyLogoutResponse;
use App\Responses\Fortify\LogoutResponse as CustomLogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // FortifyのLoginRequestを自作のものに置き換え
        $this->app->bind(FortifyLoginRequest::class, CustomLoginRequest::class);

        // 会員登録後にメール認証誘導画面に遷移するよう置き換え
        $this->app->bind(FortifyRegisterResponse::class, CustomRegisterResponse::class);

        // メール認証後のリダイレクト先を置き換え
        $this->app->bind(FortifyVerifyEmailResponse::class, CustomVerifyEmailResponse::class);

        // 初回ログイン時のリダイレクト先を設定
        $this->app->bind(FortifyLoginResponse::class, CustomLoginResponse::class);

        // Logout処理時に"/login"にリダイレクトする設定に置き換え
        $this->app->bind(FortifyLogoutResponse::class, CustomLogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register View
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // Login View
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // Register (ユーザー作成)
        Fortify::createUsersUsing(CreateNewUser::class);

        // ログイン認証
        Fortify::authenticateUsing(function ($request) {
            if (Auth::attempt([
                'email' => $request->email,
                'password' => $request->password
            ])) {
                return Auth::user();
            }
            throw ValidationException::withMessages(['password' => ['ログイン情報が登録されていません'],]);
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
