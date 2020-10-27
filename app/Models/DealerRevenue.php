<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealerRevenue extends Model
{
	protected $table = 'dealer_revenue';
	protected $fillable = ['dealer_id','amount','expiry_at'];

	public $timestamps = false;
}