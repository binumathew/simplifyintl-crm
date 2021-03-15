<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInvoiceItem extends Model
{
    protected $table = 'user_invoice_lineitems';
    protected $fillable = ['invoice_id','plan_id','description', 'quantity','price'];
    public $timestamps = false;
    
    public function plan()
    {
        return $this->hasOne('App\Models\TblPlan','id','plan_id');

    } 
}
