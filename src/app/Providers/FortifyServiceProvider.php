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
use App\Http\Requests\LoginRequest as AppLoginRequest;

use Laravel\Fortify\Contracts\LogoutResponse as FortifyLogoutResponse;
use App\Actions\Fortify\LogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // FortifyのLoginRequestを自作のものに置き換え
        $this->app->bind(FortifyLoginRequest::class, AppLoginRequest::class);

        // Logout処理時に"/login"にリダイレクトする設定に置き換え
        $this->app->singleton(FortifyLogoutResponse::class, LogoutResponse::class);
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
