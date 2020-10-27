<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Crypt;
use Excel;
use Carbon;
use Helper;
use DataTables;
use App\Models\UserPayment;
use App\Exports\CustomExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of payments.
     *
     * @return \Illuminate\Http\Response
     */
    public function list_payment_history()
    {
        if (!Helper::has_permission('payment_history') && !Helper::has_permission('payment_history','view_own')) {
            abort(403,'Access denied');
        }

        return view('payment-history');
    }

    /**
     * Display a listing of payment details.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment_history_pagination(Request $request)
    {       
        $admin_id = Auth::id();
        $where = DB::table('admins')->where('parent_id', $admin_id)->pluck('id')->toArray();        
        array_push($where, $admin_id);

    	$payments = UserPayment::select('user_payments.id','usr.name','phone','transaction_id','currency_symbol','buy_price','total_amount','card_type','payment_method','description','user_payments.status as status','user_payments.created_at')->join('users as usr','usr.id','=','user_id')->join('country as c','usr.country_id','=','c.id')->where('currency_symbol',$request->currency);

        $total_sale = UserPayment::join('users as usr','usr.id','=','user_id')
                        ->join('country as c','usr.country_id','=','c.id')
                        ->where('currency_symbol',$request->currency);
        if ($request->transaction_id) {            
            $payments->where('transaction_id',$request->transaction_id);
            $total_sale->where('transaction_id',$request->transaction_id);
        }
        if ($request->phone) {
            $phone = ltrim($request->phone, '0');            
            $payments->where('phone', 'like', '%'.$phone);
            $total_sale->where('phone', 'like', '%'.$phone);
        }
        if (!empty($request->from)) {
            $from = Carbon::parse($request->from)->format('Y-m-d');
            $payments->whereDate('user_payments.created_at', '>=', $from);
            $total_sale->whereDate('user_payments.created_at', '>=', $from);
            if($request->to){
                $to   = Carbon::parse($request->to)->format('Y-m-d');
                $payments->whereDate('user_payments.created_at', '<=', $to);
                $total_sale->whereDate('user_payments.created_at', '<=', $to);
            }
        }else{
            $from = Carbon::now()->format('Y-m-d');
            $payments->whereDate('user_payments.created_at', $from);
            $total_sale->whereDate('user_payments.created_at', $from);
        }
        if($request->method) {
            $method = ['1' => 'Paypal','2' => 'Braintree','3' => 'Bank Transfer','4' => 'Direct Cash','5' => 'In App'];
            $payment_method = $method[$request->method];
            $payments->where('user_payments.payment_method', $payment_method);
            $total_sale->where('user_payments.payment_method', $payment_method);
        }
        if (Helper::has_permission('payment_history')) {
        } elseif(Helper::has_permission('payment_history','view_own')) {
            $payments->whereIn('usr.dealer_id', $where);
        }
        
        if($request->exportdata){
            $payments = $payments->get();
            $payments = $payments->map(function ($item) {
                switch ($item->status) {
                    case 0:
                    $item->status = 'Failed';
                    break;
                    case 1:
                    $item->status = 'Success';
                    break;
                    case 2:
                    $item->status = 'Success with error';
                    break;
                    case 3:
                    $item->status = 'Refunded';
                    break;
                    case 4:
                    $item->status = 'Deduct';
                    break;
                }
                return collect($item)->except(['phone']);
            });

            $payments->prepend(array('Name','Transaction ID','Currency','Buy Price','Sell Amount','Payment Method','Description','Status','Payment Date'));
            return Excel::download(new CustomExport($payments->toArray()), 'paymenthistory.csv');
        }else{
            $total = $total_sale->where('user_payments.status',1)->sum('total_amount');
            $total_buy = $total_sale->where('user_payments.status',1)->sum('buy_price');
            $total_refund = $total_sale->where('user_payments.status',3)->sum('total_amount');
            // echo $total_refund;
            return Datatables::eloquent($payments)
                ->editColumn('name', function ($payment) {
                   return '<span class="font-600 text-muted">'.$payment->name .'</span>';  
                })->editColumn('total_amount', function ($payment) { 
                   return $payment->currency_symbol.Helper::number_format($payment->total_amount); 
                })->editColumn('created_at', function ($payment) { 
                   return Helper::date_format($payment->created_at); 
                })->editColumn('action', function ($payment) {
                    $refund = '';
                    if(Helper::has_permission('payment_history', 'edit') && $payment->status == 1){
                        $refund = '<a href="javascript:void(0);" title="Refund" class="action_refund text-danger" data-id="'.Crypt::encrypt($payment->id).'" data-amount="'.$payment->total_amount.'" data-currency="'.$payment->currency_symbol.'"><i class="mdi mdi-undo-variant mdi-24px"></i></a>';
                    }
                    return '<form class="grid_form" method="post" action="'.url('/user-details').'">'.csrf_field().'<input type="hidden" name="identifier" value="'.$payment->phone.'"><a title="View Details"  href="javascript:void(0);"  class="show_user_data text-muted m-r-10"><i class="mdi mdi-eye mdi-24px"></i></a> '. $refund .'</form>';                   
                })
                ->rawColumns(['name','action'])
                ->with(['total_sum' => Helper::number_format($total), 'total_buy' => Helper::number_format($total_buy), 'refund' => Helper::number_format($total_refund), 'currency' => $request->currency])
                ->make(true);
        }
    }

    /**
     * Display a listing of payment details.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment_refund_process(Request $request)
    {
        if (!Helper::has_permission('payment_history','edit')) {
            return response()->json(['error' => true, 'message' => 'Access denied']);
        }

        $validator = Validator::make($request->all(), ['amount' => 'required', 'description' => 'required|max:150'],
            ['amount.required' => 'Enter a valid amount','description.required' => 'Enter the description']);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
        }

        $pay_id = Crypt::decrypt($request->txn_id);
        $payment = UserPayment::where('id', $pay_id)->first();
        if($payment){
            if($payment->total_amount < $request->amount){
                return response()->json(['error' => true, 'message' => 'The amount must be equal to or less than '.$payment->total_amount.' '.$payment->currency]);
            }
            switch($payment->payment_method){
                case 'Paypal':
                    $refund = Helper::paypal_refund_process($payment, $request->amount, $request->description);
                break;
                case 'Braintree':
                    $refund = Helper::braintree_refund_process($payment, $request->amount, $request->description);
                break;
                default:
                    $refund = ['error' => true, 'message' => 'Refund process doesn\'t support for this transaction'];
                break;
            }
            return response()->json(['error' => $refund['error'], 'message' => $refund['message']]);
        }else{
            return response()->json(['error' => true, 'message' => 'Transaction details doesn\'t match']);
        }
    }
}
