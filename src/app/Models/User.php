<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'postal_code',
        'address',
        'building',
        'profile_image_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'profile_completed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // productsテーブルとのリレーション
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    // ordersテーブルとのリレーション
    public function orders()
    {
        return $this->hasMany(Order::class);
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

    // is_profile_completedの計算
    public function getIsProfileCompletedAttribute(): bool
    {
        return !is_null($this->profile_completed_at);
    }
}
