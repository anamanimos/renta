<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'variant_id', 'quantity'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute()
    {
        $days = max(1, $this->cart->total_days);
        $qty = $this->quantity;
        
        // Cek jika rujukan ini berupa Varian atau Produk reguler
        $priceBase = $this->variant ? $this->variant->price_per_day : ($this->product->promo_price ?? $this->product->price_per_day);
        $priceTier = $this->variant 
            ? ($this->variant->tier_price ?? $this->variant->price_per_day) 
            : ($this->product->tier_promo_price ?? $this->product->tier_price ?? $this->product->price_per_day);
            
        $priceType = $this->variant ? $this->variant->price_type : $this->product->price_type;

        if ($priceType === 'sell_once' || $priceType === 'beli_putus') {
            return $priceBase * $qty;
        } elseif ($priceType === 'rental_tiered' || $priceType === 'custom_pricing') {
            return ($priceBase * $qty) + ($priceTier * ($days - 1) * $qty);
        } else {
            // rental_flat atau general_pricing
            return $priceBase * $days * $qty;
        }
    }
}
