<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductComment;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    // 商品一覧画面 (商品検索も含む)
    public function index(Request $request)
    {
        $tab = $request->query('tab');
        $keyword = $request->input('keyword');

        // マイリストの場合
        if ($tab === 'mylist') {

            // いいねした商品のみ表示、出品した商品も除外
            if (Auth::check()) {
                $products = Product::whereHas('productLikes', function ($q) {
                    $q->where('user_id', Auth::id());
                })->where('user_id', '!=', Auth::id());

                // 検索条件で絞り込み
                if ($keyword) {
                    $products->where('name', 'like', '%' . $keyword . '%');
                }

                $products = $products->get();

            // 未認証の場合は何も表示しない
            } else {
                $products = collect();
            }

        // マイリスト以外の場合
        } else {
            $query = Product::query();
            // 出品した商品を除外
            if (Auth::check()) {
                $query->where('user_id', '!=', Auth::id());
            }

            // 検索条件で絞り込み
            if ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%');
            }

            $products = $query->get();
        }

        return view('items.index', compact('products'));
    }

    // 商品詳細画面の表示
    public function show($item_id)
    {
        $product = Product::with([
            'productCategories',
            'productCondition',
            'productLikes',
            'productComments.user'
        ])->findOrFail($item_id);

        return view('items.detail', compact('product'));
    }

    // いいねの処理
    public function like($item_id)
    {
        $product = Product::findOrFail($item_id);
        if ($product->productLikes()->where('user_id', Auth::id())->exists()) {
            $product->productLikes()->where('user_id', Auth::id())->delete();
        }
        else {
            $product->productLikes()->firstOrCreate(['user_id' => Auth::id()]);
        }

        return redirect("/item/{$item_id}");
    }

    // コメントの処理
    public function comment(CommentRequest $request, $item_id)
    {
        ProductComment::create([
            'user_id' => Auth::id(),
            'product_id' => $item_id,
            'comment' => $request->comment,
        ]);

        return redirect("/item/{$item_id}");
    }
}
