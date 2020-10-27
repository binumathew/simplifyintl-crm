<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffCommission extends Model
{
	protected $table = 'staff_commission';
	protected $fillable = ['role_id','target_from', 'target_to', 'commission'];

	public $timestamps = false;
}