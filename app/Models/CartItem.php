<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute()
    {
        $days = max(1, $this->cart->total_days);
        $qty = $this->quantity;
        $product = $this->product;
        
        if ($product->price_type === 'sell_once') {
            $price = $product->promo_price ?? $product->price_per_day;
            return $price * $qty;
        } elseif ($product->price_type === 'rental_tiered') {
            $basePrice = $product->promo_price ?? $product->price_per_day;
            $tierPrice = $product->tier_promo_price ?? $product->tier_price ?? $product->price_per_day;
            return ($basePrice * $qty) + ($tierPrice * ($days - 1) * $qty);
        } else {
            // rental_flat
            $price = $product->promo_price ?? $product->price_per_day;
            return $price * $qty * $days;
        }
    }
}
