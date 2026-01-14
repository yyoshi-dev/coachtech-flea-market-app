<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
