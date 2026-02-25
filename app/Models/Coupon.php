<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'is_active' => 'boolean',
        'expires_at' => 'date',
    ];
}
