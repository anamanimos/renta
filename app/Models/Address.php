<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id', 
        'label', 
        'recipient_name', 
        'phone_number', 
        'full_address', 
        'city_id', 
        'district_id', 
        'postal_code', 
        'is_main'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
