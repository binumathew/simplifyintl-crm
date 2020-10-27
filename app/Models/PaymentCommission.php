<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentCommission extends Model
{
	protected $table = 'commission_payments';
	protected $fillable = ['autoplan_id','comm_user','payment_date','payment_end','pay_amount','comm_type','comm_rate', 'is_paid', 'paid_on','user_active'];
}