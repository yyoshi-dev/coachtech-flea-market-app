<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'stripe_type'
    ];

    // ordersテーブルとのリレーション
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // 購入画面の初期表示ID
    const DEFAULT_METHOD_ID = 1;
}
