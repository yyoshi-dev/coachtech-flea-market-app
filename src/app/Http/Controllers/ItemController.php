<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCondition;
use App\Models\ProductLike;
use App\Models\ProductComment;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    // 商品一覧画面
    public function index(Request $request)
    {
        $tab = $request->query('tab');

        // マイリストの場合
        if ($tab === 'mylist') {
            if (Auth::check()) {
                $products = Product::whereHas('productLikes', function ($q) {
                    $q->where('user_id', Auth::id());
                })->get();
            } else {
                $products = collect();
            }
        } else {
            // マイリスト以外の場合
            $query = Product::query();
            if (Auth::check()) {
                $query->where('user_id', '!=', Auth::id());
            }
            $products = $query->get();
        }

        return view('items.index', compact('products'));
    }
}
