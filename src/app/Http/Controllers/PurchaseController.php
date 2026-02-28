<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

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

    /**
     * 注文確定処理 (共通化)
     * - sessionの住所情報を使ってOrderを作成する
     * - Productのsold_atを更新する
     * - 成功した場合のみsessionをクリアする
     *
     * return:
     * - 'ok'      : 注文確定成功
     * - 'exists'  : 既に購入がある (元コードの二重購入防止と同条件)
     * - 'invalid' : 住所情報が存在しない/壊れている等で確定出来ない
     */
    private function finalizePurchase(int $itemId, int $paymentMethodId): string
    {
        // sessionから購入情報を取得
        $address = session('purchase.address');

        // 住所情報が存在しない/壊れている場合は確定出来ない
        if (!is_array($address) || !isset($address['postal_code'], $address['address'])) {
            return 'invalid';
        }

        // buildingは必須項目ではない為、キーが無い場合でもエラーにならないように補正
        if (!array_key_exists('building', $address)) {
            $address['building'] = null;
        }

        // 二重購入防止
        // - 同時購入(競合)も考慮し、商品行をロックしてsold_atで判定する
        // - sold_atが既に入っていれば「購入済み」として扱う
        $created = DB::transaction(function () use ($itemId, $paymentMethodId, $address) {
            // 対象商品を行ロックして取得
            $product = Product::query()
                ->whereKey($itemId)
                ->lockForUpdate()
                ->firstOrFail();

            // 既に購入済みなら確定しない
            if ($product->sold_at !== null) {
                return false;
            }

            // 商品購入日の更新
            // - Order作成と同一トランザクション内で更新し整合性を保つ
            $product->sold_at = now();
            $product->save();

            // Orderの作成
            Order::create([
                'user_id' => Auth::id(),
                'product_id' => $itemId,
                'postal_code' => $address['postal_code'],
                'address' => $address['address'],
                'building' => $address['building'],
                'payment_method_id' => $paymentMethodId,
            ]);

            return true;
        });

        if ($created === false) {
            return 'exists';
        }

        // sessionのクリア
        session()->forget('purchase');

        return 'ok';
    }

    // 購入処理 (stripe決済画面への遷移)
    public function purchase(PurchaseRequest $request, $item_id)
    {
        // 支払い方法をセッションに追加
        session(['purchase.payment_method_id' => $request->payment_method_id]);

        // 商品情報の取得
        $product = Product::findOrFail($item_id);

        // 売約済みなら購入画面へ戻す (Stripeセッション作成前に止める)
        if ($product->sold_at !== null) {
            return redirect("/purchase/{$item_id}");
        }

        // 支払い方法の取得
        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
        $stripePaymentMethod = $paymentMethod->stripe_type;

        /*
        コンビニ支払いを選択した場合は、「購入する」ボタンを押した際に購入処理を走らせる
        カード支払いの場合は、stripeの決済画面に遷移させる
        */
        if ($stripePaymentMethod === 'konbini') {
            // 注文確定処理を実行する
            $result = $this->finalizePurchase(
                (int) $item_id,
                (int) $request->payment_method_id
            );

            // 既に購入がある場合は、トップページにリダイレクト
            if ($result === 'exists') {
                session()->forget('purchase');
                return redirect('/');
            }

            // 住所情報が存在しない/壊れている場合等は購入画面に戻す
            if ($result !== 'ok') {
                return redirect("/purchase/{$item_id}");
            }

            // 購入完了の場合は、トップページにリダイレクト
            return redirect('/');
        }

        else {
            // Stripe Checkoutの最小金額(50 JPY)未満は作成出来ない
            if ((int) $product->price < 50) {
                // 50円未満の場合は購入画面へ戻す
                return redirect("/purchase/{$item_id}")
                    ->withErrors(['payment_method_id' => 'stripeのカード決済は50円以上からしか利用出来ない。']);
            }

            // Checkout SessionをStripeClient経由で作成
            $stripe = app(StripeClient::class);

            try {
                $session = $stripe->checkout->sessions->create([
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
                    'success_url' => url("/purchase/success/{$item_id}?session_id={CHECKOUT_SESSION_ID}"),
                    'cancel_url' => url("/purchase/{$item_id}"),
                    'client_reference_id' => (string) Auth::id(),
                    'metadata' => [
                        'item_id' => (string) $item_id,
                        'payment_method_id' => (string) $request->payment_method_id,
                    ],
                ]);
            } catch (\Throwable $e) {
                return redirect("/purchase/{$item_id}");
            }

            return redirect($session->url);
        }
    }

    // 決済成功後の処理
    public function handleStripeSuccess(Request $request, $item_id)
    {
        // success_urlからCheckout Session IDを取得
        $sessionId = (string) $request->query('session_id', '');
        if ($sessionId === '') {
            return redirect("/purchase/{$item_id}");
        }

        // Stripe側からCheckout Sessionを取得し、決済が成功しているかを検証
        $stripe = app(StripeClient::class);
        try {
            $checkoutSession = $stripe->checkout->sessions->retrieve($sessionId, []);
        } catch (\Throwable $e) {
            return redirect("/purchase/{$item_id}");
        }

        // 決済状態がpaidでない、または購入者/商品が一致しない場合は注文確定しない
        if (
            ($checkoutSession->payment_status ?? '') !== 'paid'
            || (int) ($checkoutSession->client_reference_id ?? 0) !== (int) Auth::id()
            || (int) ($checkoutSession->metadata->item_id ?? 0) !== (int) $item_id
        ) {
            return redirect("/purchase/{$item_id}");
        }

        // Stripe側に埋め込んだ支払い方法IDを取得
        $paymentMethodId = (int) ($checkoutSession->metadata->payment_method_id ?? 0);
        if ($paymentMethodId === 0) {
            return redirect("/purchase/{$item_id}");
        }

        // 注文確定処理を実行する
        $result = $this->finalizePurchase(
            (int) $item_id,
            (int) $paymentMethodId
        );

        // 既に購入がある場合は、トップページにリダイレクト
        if ($result === 'exists') {
            session()->forget('purchase');
            return redirect('/');
        }

        // 住所情報が存在しない/壊れている場合等は購入画面に戻す
        if ($result !== 'ok') {
            return redirect("/purchase/{$item_id}");
        }

        // 購入完了の場合は、トップページにリダイレクト
        return redirect('/');
    }
}
