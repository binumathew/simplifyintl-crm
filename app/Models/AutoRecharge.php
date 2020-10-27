<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoRecharge extends Model
{
	protected $table = 'auto_recharge';

    protected $fillable = ['user_id', 'transaction_id', 'amount', 'tax', 'total_amount','card_expiry', 'card_id', 'card_type', 'status'];
}
