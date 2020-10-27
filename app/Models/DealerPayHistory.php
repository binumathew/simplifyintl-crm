<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealerPayHistory extends Model
{
	protected $table = 'dealer_pay_history';
	protected $fillable = ['dealer_id','amount','balance_amount','paid_on'];

	public $timestamps = false;
}