<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = [
        'name'
    ];

    // productsテーブルとのリレーション
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_category_relations');
    }
}
