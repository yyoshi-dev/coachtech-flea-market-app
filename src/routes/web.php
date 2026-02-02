<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

// 商品一覧
Route::get('/', [ItemController::class, 'index']);
Route::get('/search', [ItemController::class, 'search']);

// 商品詳細
Route::get('/item/{item_id}', [ItemController::class, 'show']);
Route::post('/item/{item_id}/like', [ItemController::class, 'like'])
    ->middleware('auth');
Route::post('/item/{item_id}/comment', [ItemController::class, 'comment'])
    ->middleware('auth');

// 認証
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// プロフィール設定 (初回ログイン時)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mypage/profile', [ProfileController::class, 'showProfileEditPage']);
    Route::post('/mypage/profile', [ProfileController::class, 'editProfile']);
});

// 商品購入
Route::middleware(['auth', 'verified', 'profile.completed'])->group(function () {
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'showPurchasePage']);
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'showAddressEditPage']);
    Route::post('/purchase/payment/{item_id}', [PurchaseController::class, 'storePaymentMethodSelection']);
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'purchase']);
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress']);
    Route::get('/purchase/success/{item_id}', [PurchaseController::class, 'handleStripeSuccess']);

    Route::get('/mypage', [ProfileController::class, 'showProfilePage']);
    // Route::get('/sell', ...);
});

// Stripe Webhook用のルート
Route::post('/stripe/webhook', [PurchaseController::class, 'handleStripeWebhook']);