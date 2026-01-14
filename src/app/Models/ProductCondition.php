<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCondition extends Model
{
    protected $fillable = [
        'name'
    ];

    // productsテーブルとのリレーション
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
