<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffCommissionPayment extends Model
{
	protected $table = 'staffcommission_payments';
	protected $fillable = ['stock_id','comm_staff','payment_date','pay_amount','comm_rate', 'is_paid', 'paid_on','comm_for'];
}