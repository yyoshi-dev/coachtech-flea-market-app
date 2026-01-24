<?php

use App\Http\Controllers\ItemController;
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

// 他画面を作成後に作成する (未認証ユーザーは自動ではじかれるはず)
// Route::middleware(['auth', 'verified'])->group(function () {
//     Route::get('/purchase/{item_id}', ...);
//     Route::get('/sell', ...);
//     Route::get('/mypage', ...);
//     Route::get('/mypage/profile', ...);
// });