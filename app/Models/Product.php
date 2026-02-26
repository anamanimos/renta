<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'wp_post_id', 'category_id', 'name', 'slug', 'description',
        'price_type', 'price_per_day', 'promo_price', 'tier_price', 'tier_promo_price',
        'stock_quantity', 'image', 'is_active'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
