<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClawbackPayments extends Model
{
	protected $table = 'clawback_payments';
	protected $fillable = ['autoplan_id','comm_user','comm_total','comm_gained','comm_reversal','clawback_amount','clawback_date'];
}