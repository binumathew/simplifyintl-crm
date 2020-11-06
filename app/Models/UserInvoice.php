<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon;
use Helper;
use App\Models\UserPlan;
use App\Models\User;

class UserInvoice extends Model
{
    protected $table = 'user_invoice';
    protected $fillable = ['user_id','invoice_number','amount', 'tax', 'total_amount', 'invoice_date', 'file_name','category'];
    public $timestamps = true;
    
    function invoice_list($data){
        $invoice = DB::table('user_invoice')
                    ->where('user_id',$data->user_id);
        if($data->from != ""){
            $from = Carbon::parse($data->from)->format('Y-m-d');
            if($data->to){
                $to   = Carbon::parse($data->to)->format('Y-m-d');
                $invoice->where('invoice_date', '>=', $from)
                            ->where('invoice_date', '<=', $to);
            }else{
                $invoice->whereRaw("date_format(invoice_date, '%Y-%m-%d')  = '$from'");
            }
        }
        $invoice = $invoice->orderBy('invoice_date','desc')->get();
        return  $invoice;
    }
    function get_plan($data){
        $getplans = DB::table('user_plans')
                ->select('user_id','created_at','id','call_cost','data_cost','sms_cost','service_total','prorata')
                ->whereDate('created_at', '>=', $data->prevfirstDay)
                ->whereDate('created_at', '<=', $data->prevlastDay)
                ->where('plan_type','<>','bridge')
                ->where('user_id',$data->user_id)
                ->get();
        if($getplans->isNotEmpty()){
            $user = User::find($data->user_id);
            foreach($getplans as $key => $list){
                $plan = UserPlan::find($list->id);
                $getamount = Helper::taxCalculation($plan->plan->sell_price,$user->country);
                $getplans[$key]->plan = $plan->plan->plan_name;
                $getplans[$key]->amount         = $getamount->amount;
                $getplans[$key]->tax_amount     = $getamount->tax_amount;
                $getplans[$key]->total_amount   = $getamount->total_amount;
            }
        }
        return $getplans;
    }
    function buy_credit($data){
        return $buy_credit = DB::table('user_payments')
                            ->where('user_id',$data->user_id)
                            ->where('category','<>','bridge')
                            ->where('payment_for','Buy Credit')
                            ->where('status',1)
                            ->whereDate('created_at', '>=', $data->prevfirstDay)
                            ->whereDate('created_at', '<=', $data->prevlastDay)
                            ->get();
    }
    function user_calls($data){
        return $user_calls = DB::table('user_calls')
                            ->where('user_id', $data->user_id)
                            ->whereDate('connect_date', '>=', $data->prevfirstDay)
                            ->whereDate('connect_date', '<=', $data->prevlastDay)
                            ->orderBy('history_from','DESC')
                            ->orderBy('connect_date')
                            ->get();
    }
    function user_data($data){
        return $user_data = DB::table('usage_history')
                            ->where('user_id', $data->user_id)
                            ->where('service_type', 'DATA')
                            ->whereDate('date', '>=', $data->prevfirstDay)
                            ->whereDate('date', '<=', $data->prevlastDay)
                            ->get();
    }
    function user_sms($data){
        return $user_sms = DB::table('usage_history')
                            ->where('user_id', $data->user_id)
                            ->where('service_type', 'SMS_MO')
                            ->whereDate('date', '>=', $data->prevfirstDay)
                            ->whereDate('date', '<=', $data->prevlastDay)
                            ->get();
    }
}