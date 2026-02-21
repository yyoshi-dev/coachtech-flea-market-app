<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // StripeClientを1回だけ生成して使いまわす
        $this->app->singleton(StripeClient::class, function () {
            return new StripeClient(config('services.stripe.secret'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // storageディレクトリに画像保存用ディレクトリを作成
        $directories = [
            'public/profiles',
            'public/products'
        ];
        foreach ($directories as $directory) {
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }
        }
    }
}
