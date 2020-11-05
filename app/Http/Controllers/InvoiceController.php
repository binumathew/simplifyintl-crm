<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Crypt;
use Carbon;
use Helper;
use MPDF;

use DataTables;

use App\Models\UserInvoice;
use App\Models\User;

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
        $datas   = explode('-', $id);
        $user_id = Crypt::decrypt($datas[0]);
        $month  = base64_decode($datas[1]);
        $year   = base64_decode($datas[2]);
        $givendate = $year.'-'.$month.'-01';
        $givendate = Carbon::parse($givendate)->endOfMonth()->format('Y-m-d');

        if(Carbon::parse($givendate)->lt(Carbon::now())){
            $prevfirstDay = Carbon::parse($givendate)->startOfMonth()->toDateString();
            $prevlastDay  = Carbon::parse($givendate)->endOfMonth()->toDateString().' 23:59:59';

            $user         = User::find($user_id);
            $arraydata    = [];
            $countinvoice = 0;
            if($user){
                $tax                = $user->country->tax;
                $username           = $user->first_name;
                $account_no         = $user->userDetail->user_platform.$user->id;
                $invoice            = UserInvoice::where('category','sim')
                                    ->where('user_id',$user_id)
                                    ->whereDate('invoice_date',$prevfirstDay)->first();
                if(!empty($invoice)){
                    $invoiceid  = explode('-',$invoice->invoice_number)[1];
                }else{
                    $invoice            = UserInvoice::where('category','sim')->max('id');  
                    $invoiceid          = ($invoice == 0) ? 10000 : $invoice+1;
                }

                $obj                = new \StdClass();
                $obj->prevfirstDay  = $prevfirstDay;
                $obj->prevlastDay   = $prevlastDay;
                $obj->tax           = $tax;

                $parent        = User::whereId($user_id)->get();
                $parentinvoice = $this->get_user_invoice($parent,$obj);
                $arraydata['parent'] = $parentinvoice;

                $child              = User::where('parent_id',$user_id)->get();
                if($child->isNotEmpty()){
                    $childinvoice = $this->get_user_invoice($child,$obj);
                    $arraydata['child'] = $childinvoice;
                }
                
                $bundlesextraamount = $bundlesextratax = $bundleextratotal = $addchargeamount = $addchargetax = $addchargetotal = $totalamount =  $totaltax = $totalbill = 0;

                foreach($arraydata as $akey => $datalist){
                    foreach($datalist as $ukey => $ulist){
                        if(isset($ulist->plan)){
                            $bundlesextraamount = $bundlesextraamount + $ulist->planamount;
                            $bundlesextratax    = $bundlesextratax + $ulist->plantax;
                            $bundleextratotal   = $bundleextratotal + $ulist->plantotal;
                        }
                        if(isset($ulist->buycredit)){
                            $bundlesextraamount = $bundlesextraamount + $ulist->creditamount;
                            $bundlesextratax    = $bundlesextratax + $ulist->credittax;
                            $bundleextratotal    = $bundleextratotal + $ulist->credittotal;
                        }
                        if(isset($ulist->plan)){
                        $addchargeamount  = $addchargeamount+$ulist->additionalamount;
                        $addchargetax     = $addchargetax + $ulist->additionaltax;
                        $addchargetotal   = $addchargetotal + $ulist->additionaltotal;
                        }
                    }
                }

                $totalamount = $bundlesextraamount + $addchargeamount;
                $totaltax    = $bundlesextratax    + $addchargetax;
                $totalbill   = $bundleextratotal   + $addchargetotal;

                $month       = strtoupper(substr(Carbon::parse($prevfirstDay)->isoFormat('MMMM'),0,2));
                $year        = Carbon::parse($prevfirstDay)->isoFormat('Y');
                $invoicefrom = Carbon::parse($prevfirstDay)->format('M d Y');
                $invoiceto   = Carbon::parse($prevlastDay)->format('M d Y');
                $invoicenumber = $month.$year.config('settings.app_prefix').'-'.$invoiceid;

                $filename = $month.'_'.$year.'_'.$invoiceid.'_'.$username.'.pdf';

                $invoicedata = [
                    'user_id' => $user_id,
                    'invoice_number' => $invoicenumber,
                    'amount' => Helper::number_format($totalamount),
                    'tax' =>Helper::number_format($totaltax),
                    'total_amount'=> Helper::number_format($totalbill),
                    'invoice_date'=>$prevfirstDay,
                    'file_name'=>$filename,
                    'category'=>'sim'
                ];

                $arraydata['invoicedata']    = $invoicedata;
                $arraydata['office_address'] = Helper::get_option('company_details');
                $arraydata['name']              = $user->first_name.' '.$user->last_name;
                $arraydata['account_no']        = $account_no;
                $arraydata['currency']          = $user->country->currency;      
                $arraydata['currency_symbol']   = $user->country->currency_symbol;
                $arraydata['billing_address']   = $user->userDetail->billing_address;
                $arraydata['bundletotal']       = Helper::number_format($bundleextratotal);
                $arraydata['addchargetotal']    = Helper::number_format($addchargetotal);

                //if($totalbill != 0){
                      $insertinvoice = UserInvoice::updateOrCreate(['user_id' => $user_id,'invoice_date'=>$prevfirstDay], $invoicedata);
                      $pdfpath  = public_path('files/invoices/'.$filename);
                      $pdf = MPDF::loadView('invoice.pdf_view', compact('arraydata'));
                      //$pdf->save($pdfpath);
                      return $pdf->download($filename);
                      return $pdf->stream($filename);
                //}
            }
        }else{

        }
    }
    private function get_user_invoice($user,$obj){
        $invoicearray = [];
        foreach($user as $ckey => $clist){
            $invoicearray[$clist->id] = json_decode(json_encode($clist));
            $invoicearray[$clist->id]->msisdn = ($clist->stock_id) ? Helper::phoneInter_format($clist->msisdn->phone_number,$clist->country->dial_code) : $clist->userDetail->user_platform.$clist->id;
            $obj->user_id   = $clist->id;
            $getplans       = $this->model->get_plan($obj);
            if( $getplans->isNotEmpty() ){
                $additional        = Helper::number_format($getplans->sum('service_total'));
                $additional        = Helper::vataddCalculation($additional,$obj->tax);
                $invoicearray[$clist->id]->plan = json_decode(json_encode($getplans));
                $invoicearray[$clist->id]->planamount = Helper::number_format($getplans->sum('amount'));
                $invoicearray[$clist->id]->plantax = Helper::number_format($getplans->sum('tax_amount'));
                $invoicearray[$clist->id]->plantotal = Helper::number_format($getplans->sum('total_amount'));
                $invoicearray[$clist->id]->additionalamount = $additional->amount;
                $invoicearray[$clist->id]->additionaltax = $additional->tax_amount;
                $invoicearray[$clist->id]->additionaltotal = $additional->total_amount;

                $invoicearray[$clist->id]->call_cost = Helper::number_format($getplans->sum('call_cost'));
                $invoicearray[$clist->id]->data_cost = Helper::number_format($getplans->sum('data_cost'));
                $invoicearray[$clist->id]->sms_cost = Helper::number_format($getplans->sum('sms_cost'));
                $invoicearray[$clist->id]->planplusaddamount   = Helper::number_format($getplans->sum('amount')) + $additional->amount;
                $invoicearray[$clist->id]->planplusaddtaxamount = Helper::number_format($getplans->sum('tax_amount'))+$additional->tax_amount;
                $invoicearray[$clist->id]->planplusaddtotalamount = Helper::number_format($getplans->sum('total_amount'))+$additional->total_amount;

            }

            $buycredit = $this->model->buy_credit($obj);

            if( $buycredit->isNotEmpty() ){
                $invoicearray[$clist->id]->creditamount = Helper::number_format($buycredit->sum('amount'));
                $invoicearray[$clist->id]->credittax = Helper::number_format($buycredit->sum('tax_amount'));
                $invoicearray[$clist->id]->credittotal = Helper::number_format($buycredit->sum('total_amount'));
                $invoicearray[$clist->id]->buycredit = json_decode(json_encode($buycredit));
            }
            $usercalls = $this->model->user_calls($obj);
            if( $usercalls->isNotEmpty() ){
                $invoicearray[$clist->id]->usercalls = json_decode(json_encode($usercalls));
            }
            $userdata = $this->model->user_data($obj);
            if( $userdata->isNotEmpty() ){
                $invoicearray[$clist->id]->userdata = json_decode(json_encode($userdata));
            }
            $usersms = $this->model->user_sms($obj);
            if( $usersms->isNotEmpty() ){
                $invoicearray[$clist->id]->usersms = json_decode(json_encode($usersms));
            }
        }
        return $invoicearray;
    }
}
