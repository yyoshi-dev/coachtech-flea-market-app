<?php

use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

// 商品一覧
Route::get('/', [ItemController::class, 'index']);

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