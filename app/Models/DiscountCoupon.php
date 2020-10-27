<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCoupon extends Model
{
    protected $fillable = ['coupon_code', 'discount_value', 'is_fixed', 'expiry_date', 'status'];

    public $timestamps = false;
}
