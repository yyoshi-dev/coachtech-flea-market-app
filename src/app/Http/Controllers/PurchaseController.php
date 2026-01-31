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
    if (!session()->has('purchase') || session('purchase.product_id') !== $item_id) {
        session()->forget('purchase');
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
        $selectedPaymentMethod = $selectedPaymentMethodId
            ? PaymentMethod::find($selectedPaymentMethodId)
            : null;

        return view('items.purchase', compact(
            'product',
            'paymentMethods',
            'selectedPaymentMethod',
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

        // ユーザー・住所情報取得
        $user = Auth::user();
        $address = session('purchase.address');

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
            'cancel_url' => url("/purchase/{$item_id}"),
            'payment_intent_data' => [
                'metadata' => [
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'postal_code' => $address['postal_code'],
                    'address' => $address['address'],
                    'building' => $address['building'],
                    'payment_method_id' => $paymentMethod->id,
                ],
            ],
        ]);

        return redirect($session->url);
    }

    // 決済成功後の処理
    public function handleStripeSuccess($item_id)
    {
        // sessionのクリア
        session()->forget('purchase');

        return redirect('/');
    }

    // Stripe Webhookの処理
    public function handleStripeWebhook(Request $request)
    {
        // Stripeから送られてくる生データ
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        // Stripeの署名認証
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
        } catch (\Exception $e) {
            return response('Invalid', 400);
        }

        // 支払い完了イベントのみの処理
        if ($event->type === 'payment_intent.succeeded') {
            $intent = $event->data->object;

            // metadataの取得
            $productId = $intent->metadata->product_id ?? null;
            $userId = $intent->metadata->user_id ?? null;
            $postalCode = $intent->metadata->postal_code ?? null;
            $address = $intent->metadata->address ?? null;
            $building = $intent->metadata->building ?? null;
            $paymentMethodId = $intent->metadata->payment_method_id ?? null;

            if ($productId && $userId && $paymentMethodId) {
                // 二重登録防止
                $exists = Order::where('user_id', $userId)
                    ->where('product_id', $productId)
                    ->exists();

                if (! $exists) {
                    // Orderの作成
                    Order::create([
                        'user_id' => $userId,
                        'product_id' => $productId,
                        'postal_code' => $postalCode,
                        'address' => $address,
                        'building' => $building,
                        'payment_method_id' => $paymentMethodId,
                        'created_at' => now(),
                    ]);

                    // 商品購入日の更新
                    Product::where('id', $productId)->update(['sold_at' => now()]);
                }
            }
        }

        // Webhook は常に 200 を返す
        return response('OK', 200);
    }
}
