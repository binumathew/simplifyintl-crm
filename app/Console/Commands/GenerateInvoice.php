<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDF;
use Carbon;
use DB;
use App\Models\User;
use App\Models\UserData;
use App\Models\UserPayment;
use App\Models\UserCalls;
use App\Models\UserInvoice;

class GenerateInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoice for the user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    

    $prevfirstDay = Carbon::now()->startOfMonth()->subMonth()->toDateString();
    $prevlastDay  = Carbon::now()->subMonth()->endOfMonth()->toDateString().' 23:59:59';

    $officeaddress = 'Gencom Cloud Service Ltd 4 Penta Court, Station Road Borehamwood, Hertfordshire WD6 1SL, UK';

    $getusers = User::where('status',1)->get();

    if( $getusers->isNotEmpty() ){

      foreach ($getusers as $gkey => $users) {
        $arraydata      = [];
        $creditamount =  $planamount = 0;
        $credittax    = $plantax = 0;
        $totalcredit = $totalcalls = $totalplan = 0;
        $totalamount = $totaltax = $totalbill = $countinvoice = 0;

        $userid       = $users->id;
        $user         = User::where('id', $userid)->first();
        $username     = $user->first_name;
        $userdata     = UserData::where('user_id',$userid)->first();

        $country      = $user->country;

        $invoice      = UserInvoice::max('id');
        $invoice      = ($invoice == "") ? 1 : $invoice+1;

        $arraydata['officeaddress'] = $officeaddress;

        if(!empty($user)){
          $arraydata['user'] = json_decode(json_encode($user));
        }
        if(!empty($userdata)){
          $arraydata['userdata'] = json_decode(json_encode($userdata));
        }
        $getplans    = DB::table("user_payments")->select('user_plans.user_id','conference_plans.plan_name','user_payments.total_amount','user_plans.created_at','user_payments.amount','user_payments.tax_amount')
                           ->join('user_plans','user_payments.id','=','user_plans.payment_id')
                           ->join('conference_plans','conference_plans.id','=','user_plans.plan_id')
                           ->where('user_payments.created_at', '>=', $prevfirstDay)
                           ->where('user_payments.created_at', '<=', $prevlastDay)
                           ->where('user_plans.plan_type','bridge')
                           ->where('user_payments.user_id',$userid)
                           ->where('user_payments.status',1)
                           ->where('user_payments.category','bridge')
                           ->get();

        if( $getplans->isNotEmpty() ){
          $planamount        = $getplans->sum('amount');
          $plantax           = $getplans->sum('tax_amount');
          $totalplan          = $getplans->sum('total_amount');
          $arraydata['totalplan'] = $totalplan;
          $arraydata['plans'] = json_decode(json_encode($getplans));
          $countinvoice++;
        }
        $buycredit = UserPayment::where('user_id',$userid)
                                  ->where('category','bridge')
                                  ->where('payment_for','Buy Credit')
                                  ->where('status',1)
                                  ->where('created_at', '>=', $prevfirstDay)
                                  ->where('created_at', '<=', $prevlastDay)
                                  ->get();    

        if( $buycredit->isNotEmpty() ){
          $creditamount           = $buycredit->sum('amount');
          $credittax              = $buycredit->sum('tax_amount');
          $totalcredit            = $buycredit->sum('total_amount');
          $arraydata['totalcredit'] = $totalcredit;
          $arraydata['buycredit'] = json_decode(json_encode($buycredit));
          $countinvoice++;
        }

        $usercalls        = UserCalls::where('user_id', $userid)
                            ->where('created_at', '>=', $prevfirstDay)
                            ->where('created_at', '<=', $prevlastDay)
                            ->get();

        if( $usercalls->isNotEmpty() ){
          $totalcalls             = $usercalls->sum('cost');
          $arraydata['totalcalls'] = $totalcalls;
          $arraydata['usercalls'] = json_decode(json_encode($usercalls));
          $countinvoice++;
        }
        $month = Carbon::parse($prevfirstDay)->isoFormat('MMMM');
        $year  = Carbon::parse($prevfirstDay)->isoFormat('Y');
        $invoiceid = str_pad($invoice, 2, '0', STR_PAD_LEFT);
        $invoicefrom = Carbon::parse($prevfirstDay)->format('M d Y');
        $invoiceto   = Carbon::parse($prevlastDay)->format('M d Y');
        $invoicenumber = 'AVC/'.$month.$year.'/'.$invoiceid;

        $filename = $month.'_'.$year.'_'.$invoiceid.'_'.$username.'.pdf';

        $totalamount = $planamount + $creditamount + $totalcalls;
        $totaltax    = $plantax + $credittax;
        $totalbill   = $totalplan + $totalcredit + $totalcalls;
        $invoicedata = [
            'user_id' => $userid,
            'invoice_number' => $invoicenumber,
            'amount' => $totalamount,
            'tax' =>$totaltax,
            'total_amount'=>$totalbill,
            'invoice_date'=>$prevfirstDay,
            'file_name'=>$filename
        ];

        if($countinvoice != 0){
          $insertinvoice = UserInvoice::firstOrCreate(['user_id' => $userid,'invoice_date'=>$prevfirstDay], $invoicedata);
          $arraydata['invoicedata'] = $invoicedata;
          $arraydata['invoicefrom'] = $invoicefrom;
          $arraydata['invoiceto']   = $invoiceto;

          if($insertinvoice->wasRecentlyCreated){
            $pdfpath  = public_path('files/invoice/'.$filename);
            $pdf = PDF::loadView('settings.pdf_view', $arraydata);
            $pdf->save($pdfpath);
          }
        }

      }
      
    }
        // $fp = fopen('userinvoice.txt', 'a+');
        // fwrite($fp, 'runs user:invoice Time : '.date('Y-m-d H:i:s'));
        // fclose($fp);
    }
}
