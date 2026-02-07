<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExhibitionRequest;
use App\Models\ProductCategory;
use App\Models\ProductCondition;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;


class ExhibitionController extends Controller
{
    public function create()
    {
        $categories = ProductCategory::all();
        $conditions = ProductCondition::all();

        return view('items.sell', compact('categories', 'conditions'));
    }

    public function store(ExhibitionRequest $request)
    {
        $product = new Product();

        // ユーザー情報の取得
        $product->user_id = Auth::id();

        // 商品情報の取得
        $product->fill($request->only([
            'name',
            'brand_name',
            'description',
            'price',
            'product_condition_id',
        ]));

        // 画像のアップロード
        if ($request->hasFile('product_image')) {
            $path = $request->file('product_image')->store('products', 'public');
            $product->product_image_path = $path;
        }

        // 商品の保存
        $product->save();

        // 商品カテゴリの登録
        $product->productCategories()->attach($request->product_category_ids);

        return redirect('/mypage');
    }
}
