<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use App\Models\Order;
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

        // sessionのリセット
        if (session('purchase.product_id') !== $item_id) {
            session()->forget('purchase.payment_method_id');
            session()->forget('purchase.address');
            session(['purchase.product_id' => $item_id]);
        }

        // session情報があれば、session情報を採用し、なければユーザー情報を採用
        $address = session('purchase.address', [
            'postal_code' => $user->postal_code,
            'address' => $user->address,
            'building' => $user->building
        ]);

        // session情報から支払い方法を選択
        $selectedPaymentMethodId = session('purchase.payment_method_id');
        $selectedPaymentMethod = PaymentMethod::find($selectedPaymentMethodId);
        // 小計や購入処理に渡す支払い方法を渡す
        $summaryPaymentMethod = $selectedPaymentMethod
            ?? PaymentMethod::find(PaymentMethod::DEFAULT_METHOD_ID);

        return view('items.purchase', compact(
            'product',
            'paymentMethods',
            'selectedPaymentMethod',
            'summaryPaymentMethod',
            'address'
            ));
    }

    // 支払い方法保存処理
    public function storePaymentMethodSelection(Request $request, $item_id)
    {
        session(['purchase.payment_method_id' => $request->payment_method_id]);
        return redirect("/purchase/{$item_id}");
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

        // sessionへの追加
        session(['purchase.address' => $address]);

        return redirect("/purchase/{$item_id}");
    }

    // 購入処理
    public function purchase(PurchaseRequest $request, $item_id)
    {
        $delivery = unserialize($request->delivery_address);

        Order::create([
            'user_id' => Auth::id(),
            'product_id' => $item_id,
            'postal_code' => $delivery['postal_code'],
            'address' => $delivery['address'],
            'building' => $delivery['building'],
            'payment_method_id' => $request->payment_method_id,
            'created_at' => now(),
        ]);

        Product::where('id', $item_id)->update(['sold_at' => now()]);

        session()->forget('purchase');

        return redirect('/'); // stripe決済画面にリダイレクトするように要修正
    }
}
