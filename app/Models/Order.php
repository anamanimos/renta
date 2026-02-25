<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'wp_order_id',
        'order_number',
        'user_id',
        'address_id',
        'start_date',
        'end_date',
        'total_days',
        'subtotal',
        'shipping_cost',
        'grand_total',
        'status',
        'payment_proof',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
