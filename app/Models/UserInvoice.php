<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class UserInvoice extends Model
{
    protected $table = 'user_invoices';
    protected $fillable = ['user_id','subscription_id','status', 'date','due_date' ,'sub_total', 'tax', 'total','amount_paid','amount_adjusted','wafe_off_amount','paid_at','deleted','credits_applied','amount_due','currency_code','next_retry_at'];
    public $timestamps = true;

    public function subscription()
    {
        return $this->hasOne('App\Models\AutoPlan','id','subscription_id');
    } 
    public function items()
    {
        return $this->hasMany('App\Models\UserInvoiceItem','invoice_id','id');
    } 

    function user_calls($user_id,$prevfirstDay,$prevlastDay){
        try{
            $get    =  DB::table('user_calls')
                        ->where('user_id', $user_id)
                        ->whereDate('connect_date', '>=', $prevfirstDay)
                        ->whereDate('connect_date', '<=', $prevlastDay)
                        ->orderBy('history_from','DESC')
                        ->orderBy('connect_date')
                        ->get();

            if($get ==  false){
                return false;
            }
            return $get;
        }catch(\Exception $e){
            return false;
        }
    }
    function user_data($user_id,$prevfirstDay,$prevlastDay){
        try{
            $get    =  DB::table('usage_history')
                        ->where('user_id', $user_id)
                        ->where('service_type', 'DATA')
                        ->whereDate('date', '>=', $prevfirstDay)
                        ->whereDate('date', '<=', $prevlastDay)
                        ->orderBy('user_id')
                        ->get();

            if($get ==  false){
                return false;
            }
            return $get;
        }catch(\Exception $e){
            return false;
        }
    }
    function user_sms($user_id,$prevfirstDay,$prevlastDay){
        try{
            $get    =  DB::table('usage_history')
                    ->where('user_id',  $user_id)
                    ->where('service_type', 'SMS_MO')
                    ->whereDate('date', '>=', $prevfirstDay)
                    ->whereDate('date', '<=', $prevlastDay)
                    ->get();

            if($get ==  false){
                return false;
            }
            return $get;
        }catch(\Exception $e){
            return false;
        }
    }
}
