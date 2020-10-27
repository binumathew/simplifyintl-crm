<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Excel;
use Helper;

use App\Exports\CustomExport;
use Illuminate\Support\Collection;

// use App\Imports\SimImport;
use App\Models\SimStock;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StockController extends Controller
{
    /**
    * Create a new controller instance.
    *
    * @return void
    */
    public function __construct()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 1000);
    }
    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function dealer_assign()
    {
        if(Auth::user()->role == 5){ 
            $dealers = DB::table('admins')->select('id','first_name','last_name')
                        ->where('id', Auth::id())->orWhere('parent_id', Auth::id())->get(); 
        }else{
            $dealers = DB::table('admins')->select('id','first_name','last_name')
                        ->whereIn('role', [1,5])->get(); 
        }

        return view('stock_assign', compact('dealers'));
    }

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function search_imsi_number(Request $request)
    {
        $imsi_from = $request->imsi_from;
        $imsi_to = $request->imsi_to;
        $dealer = (Auth::user()->role == 5)?Auth::id():1;
        $imsi_exist = SimStock::where('sim_number', $imsi_from)->orWhere('sim_number', $imsi_to)->count();

        if($imsi_exist == 2 || ($imsi_from == $imsi_to && $imsi_exist == 1)){
            $query = SimStock::select('id','phone_number','box_no','sim_number')
                        ->where('sim_number', '>=', $imsi_from)
                        ->Where('sim_number', '<=', $imsi_to)
                        ->where('status', 1);
                    if($dealer != 1){
                        $query->where('dealer_id', $dealer);
                    }
            $stock = $query->get();
            return response()->json(['success' => true, 'stock' => $stock]);
        }else{
            return response()->json(['success' => false, 'message' => 'Invalid IMSI number!']);
        }
    }

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function search_number_range(Request $request)
    {
        $number_from = $request->number_from;
        $number_to = $request->number_to;
        $dealer = (Auth::user()->role == 5)?Auth::id():1;
        $imsi_exist = SimStock::where('phone_number', $number_from)->orWhere('phone_number', $number_to)->count();

        if($imsi_exist == 2 || ($number_from == $number_to && $imsi_exist == 1)){
            $query = SimStock::select('id','phone_number','box_no')
                        ->where('phone_number', '>=', $number_from)
                        ->Where('phone_number', '<=', $number_to)
                        ->where('status', 1);
                    if($dealer != 1){
                        $query->where('dealer_id', $dealer);
                    }
            $stock = $query->get();
            return response()->json(['success' => true, 'stock' => $stock]);
        }else{
            return response()->json(['success' => false, 'message' => 'Invalid phone numbers!']);
        }
    }

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function sim_allocate(Request $request)
    {
        DB::beginTransaction();
        $data = SimStock::whereIn('id', $request->stock_id)
                ->update(['dealer_id' => $request->dealer_id]);
        if($data){
            DB::commit();
            return redirect('/sim-management')->with('message', 'Stock updated successfully');
        } else {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to update this stock, please try again');
        }
    }

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function edit_stock(Request $request)
    {
        $stock = SimStock::where('id', $request->stock_id)->first();
        
        return redirect('/sim-management')->with('message', 'Stock updated successfully');        
    }    

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function update_stock_list(Request $request)
    {
        $stock_id = $request->stock_id;
        $phone_number = $request->phone_number;
        if (substr($phone_number,0,3) == '+44') {
            $phone_number = substr($phone_number,1);
        } else if (substr($phone_number,0,1) == '0') {
            $phone_number = '44'.substr($phone_number,1);
        } else if (substr($phone_number,0,2) != '44') {
            $phone_number = '44'.$phone_number;
        } 
        if(SimStock::where('id', '!=', $stock_id)->where('phone_number', $phone_number)->exists()){
            return response()->json(['error' => true, 'message' => 'Phone number already exists']);     
        }        

        $stock = SimStock::where('id', $stock_id)->update(['phone_number' => $phone_number,'verified' => 1]);
        
        return response()->json(['success' => true, 'phone_number' => $phone_number]);    
    }








    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function import_sim()
    {  
        if (!Helper::has_permission('sim_management','create')) {
            abort(403,'Access denied');
        }
        return view('import-sim');    

        $rate_details = DB::table('rate_details_new')->get();   

        foreach($rate_details as $rate_detail){
            $rate = $rate_detail->rate_accessnumber/100;
            DB::table('rate_details_new')->where('id',$rate_detail->id)->update(['rate_wifi'=>$rate,'rate_callback'=>$rate,'rate_conference'=>$rate,'rate_accessnumber'=>$rate]);
            
        }
    }

        /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function import_sim_data(Request $request)
    {
        if (!Helper::has_permission('sim_management','create')) {
            abort(403,'Access denied');
        }
        $collection = Excel::toCollection(new \stdClass(), request()->file('file'));

        $i = 0; $rate_details = $result = [];
        foreach($collection as $datas){            
            foreach($datas as $i => $data){
                if($i == 0){
                    $i++;    
                    continue;
                }

                $rate_details[] = ['switch_id'=>3,'country_id'=>$data[0],'description'=>$data[1],'rate_wifi'=>$data[6],'rate_callback'=>$data[6],'rate_conference'=>$data[6],'rate_accessnumber'=>$data[6],'rate_type'=> ($data[4] == 1)?'L':'M'];

                // if($data[5] == 'Brand:  AVOO'){
                //     $msisdn[] = ltrim($data[3],'MISDN:  ');
                // }  
                //(1 GBP 2) (2 USD 5)   (3 EUR 6)
            }
        }
        //DB::table('rate_details_new')->insert($rate_details); 


        // $min_sheet = DB::table('tbl_rate_sheet')->get();
        // $temp = ['GBP'=>1,'USD'=>2,'EUR'=>3, 'AUD' => 4];
        // foreach ($min_sheet as $rate) {
        //     $user_rate_l = DB::table('rate_details')->where('switch_id', $temp[$rate->currency_code])
        //                 ->where('rate_type', 'L')->where('country_id',  $rate->country_id)
        //                 ->min('rate_accessnumber');
        //     $user_rate_m = DB::table('rate_details')->where('switch_id', $temp[$rate->currency_code])
        //                 ->where('rate_type', 'M')->where('country_id', $rate->country_id)
        //                 ->min('rate_accessnumber');
        //     if($user_rate_l){
        //         echo $rate->currency_code;
        //         echo $user_rate_l;
        //         echo $user_rate_m;
        //         DB::table('tbl_rate_sheet')->where('rate_id',$rate->rate_id)->update(['mobile'=> $user_rate_m,'land'=>$user_rate_l]);
        //     }
        // }
       
        print_r($rate_details);

        // $sim_log = DB::table('avoo_sim_log')->select('msisdn')
        //               ->whereDate('created_at', '>=', '2019-12-01')
        //               ->whereDate('created_at', '<=', '2019-12-31')->get();
        // $log_data = collect($sim_log)
        //         ->map(function($log){ return $log->msisdn; })->toArray();   
        // $msisdn_data =  array_unique($msisdn);          
        // $log_data = array_unique($log_data);
        
        // $result=array_diff($log_data, $msisdn_data);
        // print_r($result);
        //print_r($log_data);

        // foreach($collection as $lists){                
        //     foreach ($lists as $list) {
        //         $phone_number = $phone_number+1;
        //         $row['imsi_number'] = '89441100'.$list[0];
        //         $row['sim_number'] = '89441100'.$list[0];
        //         $row['phone_number'] = $phone_number;
        //         $row['category'] = 'normal';
        //         $row['price'] = '0.00';
        //         $row['status'] = 1;
        //         $row['dealer_id'] = 1;
        //         $row['box_no'] = 'B5/1';
        //         $row['provider'] = 'O2';
        //         $row['verified'] = 0;

        //         DB::table('tbl_sim_stock')->insert($row);  
        //     }
        // }


        // rate 
         // foreach ($tbl_rates as $rates) {
            // $mobile_rates = [];
            // foreach($rates as $kk => $rate){
            //     $out = ['place' => $rate[1], 'price'=> $rate[3], 'quick_search' => 0];

            //     DB::table('tbl_rate_dollar')->insert($out);

                // if(strpos($rate[1], 'Mobile')){
                //    $mobile_rates[$kk]['country'] = $rate[1];
                //    $mobile_rates[$kk]['mob_gbp'] = $rate[3];
                //    $mobile_rates[$kk]['mob_eur'] = $rate[4];
                //    $mobile_rates[$kk]['mob_aud'] = $rate[5];
                //    $mobile_rates[$kk]['mob_usd'] = $rate[2];
                // }
            // }
            // if(!empty($mobile_rates)){
            //     // print_r($mobile_rates);
            //     // echo chr(10).'========================================'.chr(10);
            //     $gbp_min = min(array_column($mobile_rates, 'mob_gbp'));
            //     $usd_min = min(array_column($mobile_rates, 'mob_usd'));
            //     $eur_min = min(array_column($mobile_rates, 'mob_eur'));
            //     $aud_min = min(array_column($mobile_rates, 'mob_aud'));
            //     // echo chr(10).'========================================'.chr(10);
            // }else{
            //     $gbp_min = $usd_min = $eur_min = $aud_min = '-';
            // }
            //die();
            // $currency = ['5'=>'AUD','4'=>'EUR','3'=>'GBP','2'=>'USD'];
            // $mob_min = ['5'=>$aud_min,'4'=>$eur_min,'3'=>$gbp_min,'2'=>$usd_min];
            //print_r($rates);
            // foreach($currency as $ckey => $cur){
            //     $out = ['country_code' => $rates[0][9], 'currency_code'=> $cur, 'mobile' => $mob_min[$ckey], 'land' => $rates[0][$ckey]];

            //     DB::table('tbl_rate_dollar')->insert($out);
            // }
        // }
    }
        /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function import_sim_data11(Request $request)
    {
        if (!Helper::has_permission('sim_management','create')) {
            abort(403,'Access denied');
        }
        $collection = Excel::toCollection(new \stdClass(), request()->file('file'));

        $i = 0; $rates = [];
        foreach($collection as $datas){            
            foreach($datas as $data){
                if($i == 0){
                    $i++;    
                    continue;
                }
                // if($i == 10000){
                //     break;
                // }
                if (strpos($data[1], 'Special Services') == false) {                                
                    $country_name = explode(' ', $data[1]);
                    $country = DB::table('country')->select('id','country_name','country_code')
                        ->where('dial_code', 'like', '+'.$data[0].'%')
                        ->where('country_name', 'like', '%'.$country_name[0].'%')->first();

                    $rate['prefix'] = $data[0];                    
                    $rate['description'] = $data[1];                
                    $rate['tariff'] = $data[2];     
                    if($country){
                        $rate['country_id']  = $country->id; 
                        $rate['country_name'] = $country->country_name; 
                        $rate['country_code'] = $country->country_code; 
                    }           
                    $rates[] = $rate;  
                    DB::table('tbl_tariff')->insert($rate);
                    $i++;  
                }                             
            }
        }

        // DB::table('tbl_tariff')->insert($rates); 
 
        // DB::table('tbl_rates')->truncate();
        // DB::table('tbl_rates')->insert($rates); 
        // (new Collection($rates))->downloadExcel(
        //     public_path(),
        //     $writerType = null,
        //     $headings = false
        // );
        
        // $export = new CustomExport($rates);

        // return Excel::download($export, 'a-z.xlsx');
        // return Excel::download(new CustomExport($rates), 'rates.csv');
    }

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function import_sim_data1(Request $request)
    {
        if (!Helper::has_permission('sim_management','create')) {
            abort(403,'Access denied');
        }
        $collection = Excel::toCollection(new \stdClass(), request()->file('file'));

        $sims = [];
        $_4sequence = ['1234','2345','3456','4567','5678','6789','9876','8765','7654','6543','5432','4321','3210'];
        $_3sequence = ['123','234','345','456','567','678','789','987','876','765','654','543','432','321','210'];
        $_2sequence = ['12','23','34','45','56','67','78','89','98','87','76','65','54','43','32','21','10'];
        $i = 0;
        $box_no = 0;
        foreach($collection as $datas){            
            foreach($datas as $data){ 
                if($i == 0){
                    $i++;    
                    continue;
                }
                $row['imsi_number'] = $data[0];
                $row['sim_number'] = $data[1];
                $row['phone_number'] = $data[2];
                $_5th = substr($data[2], -5, 1);
                $_4th = substr($data[2], -4, 1);
                $_3rd = substr($data[2], -3, 1);
                $_2nd = substr($data[2], -2, 1);
                $_1st = substr($data[2], -1, 1);

                $category = 'normal';
                if($_1st == $_2nd && $_2nd == $_3rd && $_3rd == $_4th){
                    $category = 'gold';
                }else if($_1st == $_2nd && $_3rd == $_4th){
                    $category = 'gold';
                }else if(in_array($_1st.$_2nd.$_3rd.$_4th , $_4sequence)){
                    $category = 'gold';
                }else if($_1st == $_4th && $_3rd == $_2nd){
                    $category = 'gold';
                }else if($_1st == $_2nd && $_2nd == $_3rd){
                    $category = 'gold';
                }else if($_1st == $_3rd && $_2nd == $_4th){
                    $category = 'gold';
                }else if(in_array($_1st.$_2nd.$_3rd , $_3sequence)){
                    $category = 'gold';
                }else if($_2nd == $_3rd && $_3rd == $_4th && $_4th == $_5th){
                    $category = 'silver';
                }else if($_2nd == $_3rd && $_3rd == $_4th){
                    $category = 'silver';
                }else if(in_array($_2nd.$_3rd.$_4th.$_5th , $_4sequence)){
                    $category = 'silver';
                }else if($_2nd == $_5th && $_3rd == $_4th){
                    $category = 'silver';
                }else if($_2nd == $_3rd && $_3rd == $_4th){
                    $category = 'silver';
                }else if($_3rd == $_4th && $_4th == $_5th){
                    $category = 'silver';
                }else if($_2nd == $_4th && $_3rd == $_5th){
                    $category = 'silver';
                }else if(in_array($_3rd.$_4th.$_5th , $_3sequence) && $_2nd == $_1st){
                    $category = 'silver';
                }else if(in_array($_2nd.$_3rd.$_4th , $_3sequence) && $_2nd == $_1st){
                    $category = 'silver';
                }else if(in_array($_1st.$_2nd , $_2sequence) || $_1st == $_2nd){
                    $category = 'silver';
                }else if(in_array($_3rd.$_4th.$_5th , $_3sequence) && $_2nd == $_3rd){
                    $category = 'silver';
                }else if($_5th.$_4th == $_2nd.$_1st){
                    $category = 'silver';
                }else if($_3rd == $_1st){
                    $category = 'silver';
                }
                // else if(in_array($_3rd.$_4th.$_5th , $_3sequence)){
                //     $category = 'silver';
                // }        
                $row['category'] = $category;
                if(($i % 500) == 1 ){
                    $box_no++; 
                }
                //SimStock 

                $res = DB::table('tbl_sim_stock')->where('phone_number', $row['phone_number'])->update(['box_no' => $box_no]);
               
                // $sims[] = $row;  
                $i++;             
            }     
            // DB::table('tbl_sim_stock')->truncate();
            // DB::table('tbl_sim_stock')->insert($sims);   
                              
        }
    }
    

    public function get_crd1()
    {
        $data = json_decode(file_get_contents(url('/').'/cdr.txt'));

        $datausage =0;
        array_walk( $data->usages, function ($value) use(&$arrayReindexed ,&$datausage){
                if($value->serviceType == 'DATA'){
                    $datausage += $value->duration;
                    $arrayReindexed['duration'][] = $value->duration;                 
                }
            }
        );
        echo $datausage.'---'; 
        print_R($arrayReindexed);
    }

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function get_crd()
    {  
        // if (!Helper::has_permission('sim_management','create')) {
        //     abort(403,'Access denied');
        // }

        $sims = DB::table('tbl_sim_stock')->paginate(10);
        return view('sims', compact('sims'));
    }

}