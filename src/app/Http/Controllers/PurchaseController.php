<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        $address = session('purchase.address', [
            'postal_code' => $user->postal_code,
            'address' => $user->address,
            'building' => $user->building
        ]);

        return view('items.purchase', compact('product', 'paymentMethods', 'address'));
    }
}
