<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'wp_inventory_id', 'name', 
        'price_type', 'price_per_day', 'tier_price', 'stock_quantity'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
