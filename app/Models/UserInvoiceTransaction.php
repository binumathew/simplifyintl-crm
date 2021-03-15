<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInvoiceTransaction extends Model
{
    protected $table = 'user_invoice_transactions';
    protected $fillable = ['invoice_id','transaction_id','payment_method_id','date', 'amount','status' ,'description', 'currency_code', 'deleted'];
    public $timestamps = true;

    public function invoice()
    {
        return $this->hasOne('App\Models\Invoice','id','invoice_id');
    } 
}
