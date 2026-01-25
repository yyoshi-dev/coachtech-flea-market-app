<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // timestampsの自動入力をオフ
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'product_id',
        'postal_code',
        'address',
        'building',
        'payment_method_id',
        'created_at'
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
