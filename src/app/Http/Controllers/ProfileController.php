<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // プロフィール画面の表示
    public function showProfilePage(Request $request)
    {
        // ページ情報の取得 (デフォルトは出品した商品)
        $page = $request->query('page', 'sell');

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

    // プロフィール編集画面の表示
    public function showProfileEditPage()
    {
        /** @var User $user */
        $user = Auth::user();

        return view('mypage.profile-edit', compact('user'));
    }

    // プロフィール編集処理
    public function editProfile(ProfileRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // 画像のアップロード処理
        if ($request->hasFile('profile_image')) {

            // 古い画像を削除 (画像の存在確認も含む)
            if ($user->profile_image_path && Storage::disk('public')->exists($user->profile_image_path)) {
                Storage::disk('public')->delete($user->profile_image_path);
            }

            // 新しい画像を保存
            $path = $request->file('profile_image')->store('profiles', 'public');
            $user->profile_image_path = $path;
        }

        // 名前、住所等の反映
        $user->fill($request->only([
            'name',
            'postal_code',
            'address',
            'building',
        ]));

        $user->save();

        // 初回プロフィール設定時の遷移先は"/"だが、通常のプロフィール設定後の遷移先は不明なので確認中
        // 普通マイページからプロフィールを編集するとマイページに戻る気もするので一旦mypageで仮置き
        return redirect('/mypage');
    }
}
