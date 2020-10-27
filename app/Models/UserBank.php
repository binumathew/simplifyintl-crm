<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBank extends Model
{
    protected $table = 'user_bank';
	
    protected $fillable = ['user_id','bank_id','card_id','first_name', 'last_name', 'account_no', 'account_type', 'bank_code','branch_code','country_id','iban','status'];
}
