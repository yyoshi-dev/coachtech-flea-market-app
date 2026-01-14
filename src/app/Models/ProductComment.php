<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductComment extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'comment'
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
