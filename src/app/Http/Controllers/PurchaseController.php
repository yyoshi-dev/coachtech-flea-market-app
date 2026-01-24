<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddressRequest;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    // 商品購入画面
    public function showPurchasePage($item_id)
    {
        $product = Product::findOrFail($item_id);
        $paymentMethods = PaymentMethod::all();
        $user = Auth::user();

        // session情報があれば、session情報を採用し、なければユーザー情報を採用
        $address = session('purchase.address', [
            'postal_code' => $user->postal_code,
            'address' => $user->address,
            'building' => $user->building
        ]);

        return view('items.purchase', compact('product', 'paymentMethods', 'address'));
    }

    // 送付先住所変更画面
    public function showAddressEditPage($item_id)
    {
        return view('items.address', compact('item_id'));
    }

    // 送付先住所変更処理
    public function updateAddress(AddressRequest $request, $item_id)
    {
        $address = $request->only([
            'postal_code',
            'address',
            'building'
        ]);

        session(['purchase.address' => $address]);

        return redirect("/purchase/{$item_id}");
    }
}
