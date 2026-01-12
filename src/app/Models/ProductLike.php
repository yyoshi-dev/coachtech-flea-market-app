<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductLike extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
    ];

    // productsテーブルとのリレーション
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    // usersテーブルとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
