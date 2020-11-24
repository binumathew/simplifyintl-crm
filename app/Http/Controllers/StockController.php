<?php



namespace App\Http\Controllers;


use Crypt;

use DB;
use Auth;
use Excel;

use Carbon;

use Helper;

use DataTables;

Use Exception;

use App\Models\User;

use App\Models\AutoPlan;

use App\Models\UserData;

use App\Models\SimStock;

use App\Models\UserPlan;

use App\Exports\CustomExport;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;



class StockController extends Controller

{

    public function __construct()

    {

        $this->middleware('auth');

    }



    /**

    * Show the sim stock.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function list_sim_stock()

    { 

        if (!Helper::has_permission('stock')) {

            abort(403,'Access denied');

        }

        $dealers = DB::table('admins')->select('admins.id as id', DB::raw('CONCAT(first_name," ",last_name) as name'))
                ->join('tbl_roles as tr', 'tr.id', '=', 'admins.role')
                ->whereIn('tr.short_code', ['ADMIN','DEALER'])->where('status', 1)->get();

        return view('stock-list', compact('dealers'));

    }



    /*

    * List sim pagination

    */

    public function list_stock_item(Request $request)

    {

        $stock = DB::table('tbl_sim_stock as ts')->select('ts.id','phone_number','sim_number','provider','category','verified','box_no','price',
                                DB::raw('CONCAT(a.first_name," ",a.last_name) as fullname'),'ts.status')->join('admins as a','dealer_id','=','a.id');



        if($request->phone_number){

            $stock = $stock->where('phone_number', $request->phone_number);

        }

        if($request->sim_number){

            $stock = $stock->where('sim_number', $request->sim_number);

        }

        if($request->dealer){

            $stock = $stock->where('dealer_id', $request->dealer);

        }

        if($request->status != ''){

            $stock = $stock->where('ts.status', $request->status);

        }



        if(isset($request->exportdata)){

            $result = collect($stock->get());

            $stock = $result->map(function ($item,$key) {

                $item->phone_number = ($item->verified == 1)?$item->phone_number:'44759xxxxxxx';

                $item->status = ($item->status == 1)?'Available':'Sold';

                return collect($item)->except(['id','verified']);

            });

            $stock->prepend(['Phone Number','Serial Number','Provider','Category','Box','Price','Dealer','Status']);

            return Excel::download(new CustomExport($stock->toArray()), 'stock.csv');

        }else{

            return $result = DataTables::queryBuilder($stock)->toJson();

        }

    }





    /*

    * List sim pagination

    */

    public function edit_sim_stock(Request $request)

    {

        $dealers = DB::table('admins')->select('admins.id as id', DB::raw('CONCAT(first_name," ",last_name) as name'))
                ->join('tbl_roles as tr', 'tr.id', '=', 'admins.role')
                ->whereIn('tr.short_code', ['ADMIN','DEALER'])->where('status', 1)->get();

        $stock =  SimStock::whereId($request->stock_id)->first();               

        $stock_details = 1;

        $html = view('modal-popup', compact('stock_details','stock','dealers'))->render();

        return response()->json(['error' => false, 'html' => $html]);

    }
    /*

    * Manage stock edit

    */

    public function manage_stock(Request $request)

    {
        $stock_id = Crypt::decrypt($request->stock_id);
        $stock    = SimStock::whereId($stock_id)->first();
        if(!empty($stock)){
            $data               = $request->except(['stock_id']);
            $data['dealer_id']  = Crypt::decrypt($data['dealer_id']);
            $update = SimStock::whereId($stock_id)
                                ->update($data);
            if($update){
                return response()->json(['error' => false, 'msg' => 'Stock updated successfully']);
            }else{
              return response()->json(['error' => true, 'msg' => 'Updation failed']);  
            }
        }else{
            return response()->json(['error' => true, 'msg' => 'Stock not found']);
        }
    }





    /*

    * Import Sim List to DB

    */

    public function import_sim_stock(Request $request)

    {   

        if (!$request->hasFile('stock_list')){

            return response()->json(['error' => true, 'message' => 'Please upload a valid stock list']); 

        }

        $extension = strtolower($request->stock_list->getClientOriginalExtension());

        if(!in_array($extension, ['csv', 'xls', 'xlsx'])){

            return response()->json(['error' => true, 'message' => 'The stock list must be a file of type: csv, xls, xlsx']);

        }



        $collection = Excel::toCollection(new \stdClass(), request()->file('stock_list'));

        $i = 0; $failed = [];

        $phone_number = DB::table('tbl_sim_stock')->where('verified', 0)->orderBy('id','desc')->value('phone_number');

        $dummy_number = ($phone_number)?$phone_number:'447590000000';

        foreach($collection as $datas){            

            foreach($datas as $data){

                if($i == 0){

                    $i++;    

                    continue;

                }

                if($data[0]){

                    $phone_number = $data[0];

                }else{

                    $dummy_number = $dummy_number+1;

                    $phone_number = $dummy_number;

                }



                $row['phone_number'] = $phone_number;

                $row['sim_number'] = $data[1];

                $row['imsi_number'] = ($data[2])?:$data[1];                            

                $row['category'] = ($data[3])?:'normal';

                $row['price'] = ($data[4])?:'0.00';

                $row['status'] = ($data[5])?:1;

                $row['dealer_id'] = ($data[6])?:1;

                $row['box_no'] = $data[7];

                $row['provider'] = $data[8];

                $row['verified'] = ($data[9])?:0;

                if(is_null($row['sim_number']) || $row['sim_number'] == ''){

                    break;

                }

                try {

                    if(!DB::table('tbl_sim_stock')->where('sim_number',  $row['sim_number'])->exists()){

                        DB::table('tbl_sim_stock')->insert($row);

                    }                    

                } catch(Exception $ex){ 

                    // return $ex->getMessage();                     

                    $row['error'] = $ex->getMessage(); 

                    $failed[] = $row;

                }                

            }

        }

        if(!empty($failed)){

            // array_unshift($failed,array('Phone Numbr','Sim Number','IMSI Number','Category', 'Price', 'Status', 'Dealer', 'Box No', 'Provider', 'Verifed'));

            // Excel::download(new CustomExport($failed), 'stock.xlsx');

            return response()->json(['error' => true, 'message' => 'Stock list imported failed','response' => json_encode($failed)]);

        }else{

            return response()->json(['error' => false, 'message' => 'Stock list successfully imported']);

        }

    }



    /**

    * Update Stock Ph.one number 

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



    /** Update Stock Ph.one number 

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function reset_stock_list(Request $request)

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



        $stock = SimStock::where('phone_number', $phone_number)->first();

        $user = User::where('stock_id', $stock->id)->first();

        if($user){

            $now = Carbon::now()->format('Y-m-d');

            $userData = UserData::where('user_id', $user->id)->first();   

            UserPlan::where('user_id', $user->id)->where('plan_type','sim')->update(['status' => 0]);

            AutoPlan::whereRaw("find_in_set($user->id,user_list)")->where('plan_type','sim')->update(['status' => 0, 'status_changeon' => $now]);

        }



        if(!is_null($stock->temp_number)){

            $old_phone = $stock->temp_number;            

        }else{

            $old_phone = substr($phone_number, -10);

        }

        $stock->phone_number = $old_phone;

        $stock->temp_number = $phone_number;

        if($user){            

            $user->stock_id = $stock_id;        

            $userData->sim_account_id = NULL;

            $userData->sim_subscription_id = NULL;

        }            



        DB::beginTransaction(); 

        try { 

            $stock->save();

            if($user){

                $user->save();

                $userData->save(); 

            }

            DB::commit(); 

        } catch (\Exception $e) {

            DB::rollback();

            return response()->json(['success' => false, 'message' => $e->getMessage()]);

        }

        return response()->json(['success' => true, 'phone_number' => $phone_number]);
    }
    /** get stock based on provider 
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function get_stock(Request $request)
    {
        $provider_id = Crypt::decrypt($request->change_providers);
        $provider    = DB::table('tbl_providers')->whereId($provider_id)->first();
        if(!empty($provider)){
            $queryData = SimStock::select('id','phone_number','box_no','sim_number')
                        ->where('status',1)
                        ->where('box_no',$request->change_boxtype)
                        ->where('provider',$provider->short_code);

            if($request->search_type == 1){
                $imsi_from  = $request->imsi_range_from;
                $imsi_to    = $request->imsi_range_to;
                $imsi_exist = SimStock::where('sim_number', $imsi_from)->orWhere('sim_number', $imsi_to)->count();
                if($imsi_exist == 2 || ($imsi_from == $imsi_to && $imsi_exist == 1)){
                    $queryData = $queryData->where('sim_number', '>=', $imsi_from)
                                            ->Where('sim_number', '<=', $imsi_to);
                }else{
                    return response()->json(['success' => false, 'message' => 'Invalid IMSI number!']); 
                }
            }else if($request->search_type == 2){
                $number_from    = $request->phone_number_from;
                $number_to      = $request->phone_number_to;
                $imsi_exist = SimStock::where('phone_number', $number_from)->orWhere('phone_number', $number_to)->count();
                if($imsi_exist == 2 || ($number_from == $number_to && $imsi_exist == 1)){
                    $queryData = $queryData->where('phone_number', '>=', $number_from)
                                            ->Where('phone_number', '<=', $number_to);
                }else{
                    return response()->json(['success' => false, 'message' => 'Invalid phone numbers!']);
                }
            }
            if(Helper::has_permission('stock')){

            }else if(Helper::has_permission('stock','view_own')){
                $queryData = $queryData->where('dealer_id',Auth::id());
            }
            if($request->stock_limit != ""){
                $queryData = $queryData->limit($request->stock_limit);
            }
            $getstock  = $queryData->orderBy('sim_number','ASC')->get();
            return response()->json(['success' => true, 'message' =>'' ,'stock_list'=>json_encode($getstock)]);
        }else{
            return response()->json(['success' => false, 'message' => 'Provider details not found']);
        }
    }
    /** get box 
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function get_box(Request $request)
    {
        $provider_id = Crypt::decrypt($request->provider);
        $provider    = DB::table('tbl_providers')->whereId($provider_id)->first();

        if(!empty($provider)){
            $getbox = DB::table('tbl_sim_stock')
                    ->select('box_no')
                    ->where('provider',$provider->short_code)
                    ->groupBy('provider')->get();
            return response()->json(['success' => true, 'message' =>'' ,'box'=>json_encode($getbox)]);
        }else{
            return response()->json(['success' => false, 'message' => 'Provider details not found']); 
        } 
    }
    /** assign stock to the dealer
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function assign_stock(Request $request)
    {
        DB::beginTransaction();
        $data = SimStock::whereIn('id', $request->stock_id)
                ->update(['dealer_id' => Crypt::decrypt($request->dealer_id)]);
        if($data){
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Stock updated successfully']); 
        } else {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to update this stock, please try again']);
        }
    }
}
