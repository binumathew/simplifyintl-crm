<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCreditCard extends Model
{
    protected $table = 'user_credit_cards';
	
    public $timestamps = false;

    protected $fillable = ['user_id', 'transaction_id', 'card_expiry', 'card_type', 'gateway', 'is_default'];
}
