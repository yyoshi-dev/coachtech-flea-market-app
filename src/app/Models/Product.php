<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'brand_name',
        'description',
        'price',
        'product_condition_id',
        'product_image_path',
        'likes_count',
        'comments_count',
    ];

    // usersテーブルとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // product_conditionsテーブルとのリレーション
    public function productCondition()
    {
        return $this->belongsTo(ProductCondition::class);
    }
    // product_likesテーブルとのリレーション
    public function productLikes()
    {
        return $this->hasMany(ProductLike::class);
    }
    // product_commentsテーブルとのリレーション
    public function productComments()
    {
        return $this->hasMany(ProductComment::class);
    }
    // ordersテーブルとのリレーション
    public function order()
    {
        return $this->hasOne(Order::class);
    }
    // product_category_relationsテーブルとのリレーション
    public function productCategories()
    {
        return $this->belongsToMany(ProductCategory::class, 'product_category_relations');
    }

    // is_soldの計算
    public function getIsSoldAttribute()
    {
        return $this->sold_at !== null;
    }
}
