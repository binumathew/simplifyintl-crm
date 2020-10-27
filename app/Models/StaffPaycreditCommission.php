<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffPaycreditCommission extends Model
{
	protected $table = 'staff_paycredit_commission';
	protected $fillable = ['role_id','target_from', 'target_to', 'commission'];

	public $timestamps = false;
}