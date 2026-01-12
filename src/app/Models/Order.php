<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'postal_code',
        'address',
        'building',
        'payment_method_id'
    ];

    // usersテーブルとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // productsテーブルとのリレーション
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    // payment_methodsテーブルとのリレーション
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
