<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Crypt;
use Carbon;
use Helper;
use MPDF;
use DB;


use DataTables;

use App\Models\UserInvoice;
use App\Models\User;
use App\Models\TblPlan;
use App\Models\UserPaymentRequest;
use App\Models\UserPayment;

class InvoiceController extends Controller
{
    public function __construct(UserInvoice $model)
    {
        $this->model = $model;
    }
    /**
    * Show the user invoice list.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function invoice_list(Request $request)
    {
        $data           = new \stdClass(); 
        $user_id        = Crypt::decrypt($request->user_id);
        $user           = User::find($user_id);
        $usertimezone   = $user->time_zone;
        $currency       = $user->country->currency_symbol;

        $data->user_id  = $user_id;
        $data->from     = isset($request->from) ? $request->from : '';
        $data->to       = isset($request->to) ? $request->to : '';
        $invoice        = $this->model->invoice_list($data);

        return $result = DataTables::of($invoice)->addIndexColumn()
         ->editColumn('year', function ($data) {
            return Carbon::parse($data->invoice_date)->format('Y');
        })
        ->editColumn('month', function ($data) {
            return Carbon::parse($data->invoice_date)->format('M');
        })
         ->editColumn('amount', function ($data) use($currency) {
            return $currency.$data->amount;
        })
         ->editColumn('vat', function ($data) use($currency) {
            return $currency.$data->tax;
        })
         ->editColumn('total', function ($data) use($currency) {
            return $currency.$data->total_amount;
        })
          ->editColumn('downloadurl', function ($data){
            $dt = explode('-', $data->invoice_date);
            return base64_encode($dt[1]).'-'.base64_encode($dt[0]);
        })
        ->toJson();
    }
    /**
    * generate the user invoice.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function generate_invoices($id)
    {
        $datas      = explode('-', $id);
        $user_id    = Crypt::decrypt($datas[0]);
        $year       = base64_decode($datas[1]);
        $month      = base64_decode($datas[2]);
        $day        = base64_decode($datas[3]);
        $givendate  = $year.'-'.$month.'-'.$day;

        if(Carbon::parse($givendate)->lte(Carbon::now())){
            $user       = User::whereId($user_id)->first();

            if(in_array($user->msisdn->provider,['E_SIM'])){
                $invoiceDate  = Carbon::parse($givendate)->toDateString();
            }else{
                $invoiceDate  = Carbon::parse($givendate)->startOfMonth()->toDateString();
            }
            $invoice    = UserInvoice::where('user_id',$user_id)
                                    ->whereDate('date',$invoiceDate)
                                    ->first();  
            if($invoice){
                if(in_array($user->msisdn->provider,['E_SIM'])){
                    $invoiceDate  = Carbon::parse($givendate)->toDateString();
                    $prevfirstDay = Carbon::parse($invoice->date)->subDays($invoice->subscription->plan->period)->toDateString();
                    $prevlastDay  = Carbon::parse($invoice->date)->subDays()->toDateString().' 23:59:59';
                    $diff_in_months = 1;
                }else{
                    $prevfirstDay = Carbon::parse($givendate)->subMonth()->startOfMonth()->toDateString();
                    $prevlastDay  = Carbon::parse($givendate)->subMonth()->endOfMonth()->toDateString().' 23:59:59';
                    $diff_in_months = Carbon::parse($invoice->subscription->start_date)->diffInMonths(Carbon::parse($invoiceDate));
                }
                $alreadytaken = collect();
                if($diff_in_months == 0){
                    $alreadytaken = UserPayment::where('user_id',$user_id)
                                    ->whereDate('created_at','<=',Carbon::parse($invoice->subscription->start_date)->endOfMonth()->toDateString())->get();
                }
                $credit     = UserPaymentRequest::where('user_id',$user_id)->where('status',1)->get();
                $usercalls  = $this->model->user_calls($user_id,$prevfirstDay,$prevlastDay);
                $userdata   = $this->model->user_data($user_id,$prevfirstDay,$prevlastDay);
                $usersms    = $this->model->user_sms($user_id,$prevfirstDay,$prevlastDay);

                $planData = collect();
                $addData  = collect();
                $invoice->items->each(function ($item, $key) use($user,&$planData,&$addData){
                    $getcal = Helper::vataddCalculation($item->price,$user->country->tax);
                    if($item->plan_id != ""){
                        $planprice = Helper::vatreduceCalculation($item->plan->sell_price,$user->country->tax);
                        $planData[] = (object)[
                            'plan' => $item->plan,
                            'price'=>$planprice,
                            'details' => $item,
                            'amount'=> $getcal->amount,
                            'tax'=>$getcal->tax_amount,
                            'total'=>$getcal->total_amount
                        ];
                    }else{
                        $addData[] = (object)[
                            'details' => $item,
                            'amount'=> $getcal->amount,
                            'tax'=>$getcal->tax_amount,
                            'total'=>$getcal->total_amount
                        ];
                    }
                });
                $invoiceData = (object)[
                    'invoice'=>$invoice,
                    'details'=> (object)[
                        'account_no' =>$user->userDetail->user_platform.$user->id,
                        'invoice_number'=>Config('settings.app_prefix').'_INV'.str_pad($invoice->id,5,0,STR_PAD_LEFT),
                        'office_address'=>Helper::get_option('company_details'),
                    ],
                    'subscription'=> (object)[
                        'amount'=>$planData->sum('amount'),
                        'all'=>$planData
                    ],
                    'additional'=> (object)[
                        'amount'=>$addData->sum('amount'),
                        'all'=>$addData
                    ],
                    'alreadytaken'=>$alreadytaken,
                    'credit'=>$credit,
                    'calls'=>$usercalls,
                    'data'=>$userdata,
                    'sms'=>$usersms,
                    'amounttotal'=> (float)$planData->sum('amount') + (float)$addData->sum('amount'),
                    'total'=>(float)$planData->sum('total') + (float)$addData->sum('total')
                ];
                //$view = view('invoice.user_invoice', compact('invoiceData','user'))->render();
                $filename = $month.$year.'_'.$invoice->id.'_'.$user->userDetail->user_platform.$user->id.'.pdf';
                $pdf = MPDF::loadView('invoice.user_invoice', compact('invoiceData','user'));
                //return $pdf->download($filename);
                return $pdf->stream($filename);
            }else{
                dd('Invoice Details not found.');
            }
        }else{
            dd('Cannot Generate future month invoice');
        }
    }
}
