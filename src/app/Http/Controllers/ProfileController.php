<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        // ページ情報の取得 (デフォルトは出品した商品)
        $page = $request->query('page', 'sell');

        // ユーザー情報の取得
        /** @var User $user */
        $user = Auth::user();

        // 出品した商品の場合
        if ($page === 'sell') {

            $products = Product::where('user_id', $user->id)->get();

        // 購入した商品の場合
        } elseif ($page === 'buy') {
            $products = $user->orders()
                ->with('product')
                ->get()
                ->pluck('product');

        // 不正値が来た場合はsellにフォールバック
        } else {
            $products = Product::where('user_id', $user->id)->get();
            $page = 'sell';
        }

        return view('mypage.profile', compact('products', 'user', 'page'));
    }
}
