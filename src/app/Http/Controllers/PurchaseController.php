<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\Stripe;

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

        // session情報があればそれを使い、なければユーザー情報をsessionに保存
        if (!session()->has('purchase.address')) {
            session(['purchase.address' => [
                'postal_code' => $user->postal_code,
                'address' => $user->address,
                'building' => $user->building
            ]]);
        }
        $address = session('purchase.address');

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

    // 購入処理 (stripe決済画面への遷移)
    public function purchase(PurchaseRequest $request, $item_id)
    {
        // 商品情報の取得
        $product = Product::findOrFail($item_id);

        // 支払い方法の取得
        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
        $stripePaymentMethod = $paymentMethod->stripe_type;

        // stripe秘密キーの設定
        Stripe::setApiKey(config('services.stripe.secret'));

        // Checkout Sessionの作成
        $session = Session::create([
            'payment_method_types' => [$stripePaymentMethod],
            'line_items' => [[
                'price_data'=> [
                    'currency' => 'jpy',
                    'product_data' => ['name' => $product->name],
                    'unit_amount' => $product->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => url("/purchase/success/{$item_id}"),
            'cancel_url' => url("/purchase/{$item_id}")
        ]);

        return redirect($session->url);
    }

    // 決済成功処理
    public function handleStripeSuccess($item_id)
    {
        // sessionから購入情報を取得
        $address = session('purchase.address');
        $paymentMethodId = session('purchase.payment_method_id');

        // Orderの作成
        Order::create([
            'user_id' => Auth::id(),
            'product_id' => $item_id,
            'postal_code' => $address['postal_code'],
            'address' => $address['address'],
            'building' => $address['building'],
            'payment_method_id' => $paymentMethodId,
            'created_at' => now(),
        ]);

        // 商品購入日の更新
        Product::where('id', $item_id)->update(['sold_at' => now()]);

        // sessionのクリア
        session()->forget('purchase');

        return redirect('/');
    }
}
