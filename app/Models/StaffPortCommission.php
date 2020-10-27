<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffPortCommission extends Model
{
	protected $table = 'staff_porting_commission';
	protected $fillable = ['role_id','target_from', 'target_to', 'commission'];

	public $timestamps = false;
}