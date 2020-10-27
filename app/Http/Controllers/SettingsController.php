<?php



namespace App\Http\Controllers;



use DB;

use Crypt;

use Helper;

use DataTables;

use App\Models\DiscountCoupon;

use App\Models\ScheduledTask;

use App\Models\RolePermissions;

use App\Jobs\CronTaskJob;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;





use Auth;

use Hash;

use Carbon;

use App\Models\AutoPlan;

use App\Models\UserPlan;

use App\Models\Admins;

use App\Models\Options;






class SettingsController extends Controller

{

    /**

    * Create a new controller instance.

    *

    * @return void

    */

    public function __construct()

    {        

        $this->middleware('auth');

    }



    /**

    * Show the Genral Settings.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function index()

    {

        if (!Helper::has_permission('settings')) {

            abort(403,'Access denied');

        }
        $optionsdata    = [];
        $getoptions     = DB::table('options')->get();
        foreach ($getoptions as $gkey => $glist) {
            $optionsdata[$glist->category][] = $glist; 
        }
        $options = isset($optionsdata['general']) ? $optionsdata['general'] : [];
        $company = isset($optionsdata['company']) ? $optionsdata['company'][0] : [];
        $sms     = isset($optionsdata['sms']) ? $optionsdata['sms'] : [];
        $switch  = isset($optionsdata['switch']) ? $optionsdata['switch'] : [];
        return view('settings.general', compact('options','company','sms','switch'));

    }
    /**

    * Update the custom settings file.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function update_custom_settings(Request $request)

    {
        $request = $request->except(['_token']);
        if($request['sim_stock_box_category']){
            $options        = DB::table('options')->where('name','sim_stock_box_category')->first();
            $simstock       = json_decode($options->value,true);
            foreach ($request['sim_stock_box_category'] as $skey => $sval) {
               $simstock[$skey] = $sval;
            }
            unset($request['sim_stock_box_category']);
            $simstock  = json_encode($simstock);
            DB::table('options')->where('name','sim_stock_box_category')->update(['value'=>$simstock]);
        }

        foreach ($request as $key => $data ) {
            $update = [
                'value' => $data,
            ];
            DB::table('options')->where('name',$key)->update($update);
        }
        return response()->json(['status'=>200,'msg'=>'custom settings details updated']);
    }
    /**

    * Update the custom settings of conference.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function custom_changesettings(Request $request)

    {
        $type         = $request->type;
        $key          = $request->param;
        $value        = $request->toggle;

        $options        = DB::table('options')->where('name','conference_settings_name')->first();
        $settings       = json_decode($options->value,true);
        switch ($type) {
            case 'value':
              $settings[$key]['value'] = (int)$value;
              break;
            case 'changeable':
              $settings[$key]['changeable'] =  ((int)$value == 1)?true:false;
              break;
            default:
              break;
        }
        $settings  = json_encode($settings);
        DB::table('options')->where('name','conference_settings_name')->update(['value'=>$settings]);
        return response()->json(['status'=>200,'msg'=>'custom settings details changed']);
    }
    /**

    * Update the company details settings.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function update_company_settings(Request $request)
    {
        $rules = array('company_name' => 'required|min:2|max:50','company_website' => 'required|max:50','company_phone'=>'required|min:8|max:15','company_email'=>'required|email|max:30','company_street'=>'nullable|min:3|max:30','company_city'=>'nullable|min:3|max:30','company_state'=>'nullable|min:3|max:30','company_country'=>'required','company_postcode'=>'nullable|min:3|max:15','company_vat'=>'nullable|min:3|max:20','company_dialcode'=>'required|max:6','company_cntry_code'=>'required|max:3');
      $messages = array(
        'company_name.required' => 'Please provide company name',
        'company_name.min' => 'Company name minimum character required',
        'company_name.max' => 'Company name maximum character exceeded',
        'company_website.required' => 'Please provide company website',
        'company_website.max' => 'Company website maximum character exceeded',
        'company_phone.required' => 'Please provide phone number',
        'company_phone.min' => 'Phone number minimum digits required',
        'company_phone.max' => 'Phone number maximum digits exceeded',
        'company_email.required' => 'Please provide company email address',
        'company_email.email' => 'Please provide a valid company email address',
        'company_email.max' => 'Company email maximum character exceeded',
        'company_street.min' => 'Company street minimum character required',
        'company_street.max' => 'Company street maximum character exceeded',
        'company_city.min' => 'Company city minimum character required',
        'company_city.max' => 'Company city maximum character exceeded',
        'company_state.min' => 'Company state minimum character required',
        'company_state.max' => 'Company state maximum character exceeded',
        'company_country.required' => 'Please choose your country',
        'company_postcode.min' => 'Company postcode minimum character required',
        'company_postcode.max' => 'Company postcode maximum character exceeded',
        'company_vat.min' => 'Company vat number minimum character required',
        'company_vat.max' => 'Company vat number maximum character exceeded',
        'company_dialcode.required' => 'Company dial code is required',
        'company_dialcode.max' => 'Company dial code maximum character exceeded',
        'company_cntry_code.min' => 'Company country code is required',
        'company_cntry_code.max' => 'Company country code maximum character exceeded',
        );
        $validator = Validator::make($request->all(), $rules,$messages);

        // Validate the input and return correct response
        if ($validator->fails()){
          return response()->json(['status'=>422,'msg'=>$validator->errors()->all()]);
        }else{
            $request = $request->except(['_token']);
            $companydetails = json_encode($request);
            Options::updateOrCreate(['category'=>'company','name' => 'company_details'], ['category'=>'company','name' => 'company_details','value'=>$companydetails]);
             return response()->json(['status'=>200,'msg'=>'Company details updated']);

        }
    }
    /**

    * Update the sms settings.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function update_sms_settings(Request $request)

    {
        $request = $request->except(['_token']);

        foreach ($request as $key => $data ) {
            $update = [
                'value' => $data,
            ];
            DB::table('options')->where('name',$key)->update($update);
        }
        return response()->json(['status'=>200,'msg'=>'sms settings details updated']);
    }
    /**

    * Update the switch settings.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function update_switch_settings(Request $request)

    {
        $request = $request->except(['_token']);

        foreach ($request as $key => $data ) {
            $update = [
                'value' => $data,
            ];
            DB::table('options')->where('name',$key)->update($update);
        }
        return response()->json(['status'=>200,'msg'=>'Switch settings details updated']);
    }
    /**

    * add settings form.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function add_settings_form(Request $request)
    {
        $category = Options::query()->distinct()->pluck('category');
        $category = ($category->isNotEmpty()) ? $category : [];
        $view     = view('settings.create-data',compact('category'))->render();
        return response()->json(['status'=>200,'page'=>$view,'title'=>'Add Settings']);  
    }
    /**

    * add settings data.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function add_settings_data(Request $request)
    {
        $rules = array('option_name' => 'required','option_value' => 'required');
        $messages = array(
        'option_name.required' => 'Please provide name',
        'option_value.required' => 'Please provide value',
        );
        $validator = Validator::make($request->all(), $rules,$messages);

        // Validate the input and return correct response
        if ($validator->fails()){
          return response()->json(['status'=>422,'msg'=>$validator->errors()->all()]);
        }else{

            $optionname  = $request->option_name;
            $optionvalue = $request->option_value;
            $optioncat   = (isset($request->option_category)) ? $request->option_category : "";
            $data = [
                        'name' => $optionname,
                        'value' => $optionvalue,
                        'category' => $optioncat,
                    ];
            $insert = DB::table('options')->insert($data); 
            if($insert){
                return response()->json(['status'=>200,'msg'=>'Settings added..']);  
            }else{
                return response()->json(['status'=>422,'msg'=>['Technical error..']]);
            }
        }
    }
    /**

    * upload company images .

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function upload_company_images(Request $request)
    {
        $reqimage   = $request->file('images');
        $type       = $request->type;
        $fileArray  = array('image' => $reqimage);
        switch ($type) {
            case 'logo':
                $rules      = ['image' => 'mimes:png|required|max:2048'];
                $filename   = 'logo.png';
                break;
            case 'favicon':
                $rules      = ['image' => 'mimes:ico|required|max:1024'];
                $filename   = 'favicon.ico';
                break;
            
            default:
                # code...
                break;
        }
        // Now pass the input and rules into the validator
        $validator = Validator::make($fileArray, $rules);
        if ($validator->fails()) {
            return response()->json(['status'=>422,'msg'=>$validator->errors()->all()]);
        }else{
            // $upload = $reqimage->move(public_path().'/report/assets/images/', $filename);
            $upload = $reqimage->move(public_path().'/images/', $filename); 
            if($upload){
               return response()->json(['status'=>200,'msg'=>'Image updated..']); 
           }else{
               return response()->json(['status'=>422,'msg'=>['Technical error..']]);
           }
        }
        
    }
    

    /**

    * Show the Role Management.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function list_role()

    {

        if (!Helper::has_permission('roles')) {

            abort(403,'Access denied');

        }

        $roles = DB::table('tbl_roles')->get();

        return view('settings.roles', compact('roles'));

    }



    /**

    * Manage Role and Permission.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function manage_role($payload = '')

    { 

        if (!Helper::has_permission('roles','edit')) {

            abort(403,'Access denied');

        }

        $id = 1; $role = [];                

        if($payload){

            $id = Crypt::decrypt($payload);

            $role = DB::table('tbl_roles')->where('id', $id)->first();

        }

        $permissions = DB::table('tbl_permissions')->select('tbl_permissions.id as permission_id','name','shortname','can_view','can_view_own','can_create'

                        ,'can_edit','can_delete')

                        ->leftJoin('tbl_role_permissions', function($join) use ($id){

                            $join->on('tbl_permissions.id', '=', 'tbl_role_permissions.permission_id')

                            ->where('tbl_role_permissions.role_id', $id);

                        })->get();



        return view('settings.role-permission', compact('permissions','role'));

    }



    /**

    * Update the permissions.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function save_role(Request $request)

    {         

        $validator = Validator::make($request->all(), ['name'=>'required|max:30', 'short_code'=>'required|max:15']);



        if ($validator->fails()){

            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);

        }



        if($request->role_id){

            if (!Helper::has_permission('roles','edit')) {

                return response()->json(['error' => true, 'message' => 'Access denied']);

            }

            $role_id = Crypt::decrypt($request->role_id);

            if(DB::table('tbl_roles')->where('name', $request->name)->where('id','!=',$role_id)->exists()){

                return response()->json(['error' => true, 'message' => 'This role already exist']);

            }

            DB::table('tbl_roles')->where('id', $role_id)

                ->update(['name' => $request->name, 'short_code' => $request->short_code]); 

            $message = 'Role updated successfully';        

        }else{

            if (!Helper::has_permission('roles','create')) {

                return response()->json(['error' => true, 'message' => 'Access denied']);

            }

            if(DB::table('tbl_roles')->where('name', $request->name)->exists()){

                return response()->json(['error' => true, 'message' => 'This role already exist']);

            }

            $role_id = DB::table('tbl_roles')->insertGetId(['name' => $request->name, 'short_code' => $request->short_code]);

            $message = 'Role created successfully'; 

        }



        $permission_id = $request->permission_id;

        $permission_view = $request->permission_view;

        $permission_view_own = $request->permission_view_own;

        $permission_create = $request->permission_create;

        $permission_edit = $request->permission_edit;

        $permission_delete = $request->permission_delete;



        foreach($permission_id as $key => $value){

            $data['can_view'] = isset($permission_view[$value])?:0;

            $data['can_view_own'] = isset($permission_view_own[$value])?:0;

            $data['can_create'] = isset($permission_create[$value])?:0;

            $data['can_edit'] = isset($permission_edit[$value])?:0;

            $data['can_delete'] = isset($permission_delete[$value])?:0;



            RolePermissions::updateOrCreate(

                ['permission_id' => $value, 'role_id' => $role_id], $data

            );

        }



        DB::table('admins')->where('role', $role_id)->update(['force_logout' => 1]);

        return response()->json(['error' => false, 'message' => $message]);

    }





    /*

    * Delete a new role

    */

    public function delete_role(Request $request)

    {

        if (!Helper::has_permission('roles','delete')) {

            abort(403,'Access denied');

        }

    }



    /**

    * Function API logger page.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function api_logger(Request $request)

    {         

        return view('api-log');

    }



    /**

    * API log listing.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function list_api_log(Request $request)

    { 

        $log = DB::table('api_log as al')->select('al.*','usr.name')->leftJoin('users as usr','usr.id','=','user_id')

                    ->orderBy('al.created_at','desc');

        return DataTables::of($log)->make(true);

    }



    /**

    * Function List Scheduled Tasks.     

    * @return List tasks

    */

    public function scheduled_task()

    {

        $tasks = ScheduledTask::all();            

        return view('settings.scheduled-tasks',compact('tasks'));

    }



    /**

    * Function Add/Edit Scheduled Task.     

    * @return Manage tasks

    */

    public function manage_scheduled_task($id='')

    {   

        $scheduled_task = true;

        if($id){

            $task = ScheduledTask::where('id', $id)->first();

            return view('modal-popup',compact('scheduled_task','task'))->render();

        } else {

            return view('modal-popup',compact('scheduled_task'))->render();

        }

    }



    /**

    * Function Save Scheduled Tasks.     

    * @return Save tasks

    */

    public function save_scheduled_task(Request $request)

    {        

        ScheduledTask::updateOrCreate(['id' => $request->id],['description' => $request->description, 'command' => $request->command,'status' =>$request->status]);

        if($request->id)

            $message = 'Updated successfully';

        else 

            $message = 'Added successfully';

        return redirect('settings.scheduled-tasks')->with('message',$message); 

    }



    /**

    * Function Delete Scheduled Tasks.     

    * @return Delete tasks

    */

    public function delete_scheduled_task(Request $request)

    {

        ScheduledTask::where('id', $request->task_id)->delete();

        return 1;

        // return redirect('scheduled-tasks')->with('message','Deleted successfully'); 

    }



    public function execute_scheduled_task(Request $request)

    {   

        $task = ScheduledTask::select('description','command')

                ->where(['id' => $request->task_id, 'status' => 1])->first();



        if($task){

            $obj = (object) [ 'description' => $task->description, 'command'=> $task->command];  

            CronTaskJob::dispatch($obj)

                ->delay(now()->addMinutes(1));

        }

    }



    /**

    * Show the whitelisted IP.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function firewall()

    { 

        if (!Helper::has_permission('firewall')) {

            abort(403,'Access denied');

        }



        $firewall = DB::table('tbl_whitelist')->get();

        return view('settings.firewall', compact('firewall'));

    }



    /**

    * Function Add/Update whitelisted IP.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function save_firewall(Request $request)

    { 

        if(isset($request->id)) {

            if (!Helper::has_permission('firewall', 'edit')) {

                abort(403,'Access denied');

            }



            $data = $request->all();

            unset($data['_token']);



            $response = DB::table('tbl_whitelist')->whereId($request->id)->update($data);  

            if($response) {

                return redirect('/firewall')->with('message', 'IP List updated successfully!');

            } else {

                return redirect('/firewall')->with('error', 'Failed to update this, please try again');

            }

        } else {

            if (!Helper::has_permission('firewall', 'create')) {

                abort(403,'Access denied');

            }



            $data = $request->all();

            unset($data['_token']);

            $id = DB::table('tbl_whitelist')->insertGetId($data);

            if($id)

                return redirect('/firewall')->with('message','IP added successfully!');

            else

                return redirect('/firewall')->with('error', 'Failed to add this ip, please try again');

        } 

    }



    /**

    * Function delete whitelisted IP.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function delete_firewall(Request $request)

    { 

        if (!Helper::has_permission('firewall', 'delete')) {

            abort(403,'Access denied');

        }

        DB::table('tbl_whitelist')->where('id',$request->id)->delete();

        return redirect('/firewall')->with('message','IP Removed successfully!');

    }   



    /**

    * Show the throttles IP.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function throttles()

    { 

        if (!Helper::has_permission('fraudsters')) {

            abort(403,'Access denied');

        }

        return view('settings.throttles');

    } 



    /**

    * Show the throttles list.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function throttle_list(Request $request)

    { 

        if (!Helper::has_permission('fraudsters')) {

            abort(403,'Access denied');

        }



        $throttle = DB::table('throttles');

        if($request->identifier){

            $throttle = $throttle->where('identifier','like','%'. $request->identifier .'%');

        }

        if($request->ip_address){

            $throttle = $throttle->where('ip_address','like','%'. $request->ip_address .'%');

        }

        if($request->attempted){

            $throttle = $throttle->whereDate('attempted_at', Carbon::parse($request->attempted)->format('Y-m-d'));

        }

        return DataTables::queryBuilder($throttle)->toJson();

    }



    /**

    * Function delete throttle.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function delete_throttle(Request $request)

    { 

        if (!Helper::has_permission('fraudsters','delete')) {

            return response()->json(['error' => true, 'message' => 'Access denied']);

        }



        $delete = DB::table('throttles')->whereIn('id',$request->selected)->delete();

        if($delete)

            return response()->json(['error' => false, 'message' => 'Failed to update status']); 

        else

            return response()->json(['error' => true, 'message' => 'Failed to delete item(s), please try later']);

    }



    /**

    * Show the list of countries.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function list_countries()

    { 

        if (!Helper::has_permission('countries')) {

            abort(403,'Access denied');

        }



        $countries = DB::table('country')->get();

        return view('settings.country-list', compact('countries'));

    }



    /**

    * Edit country

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function add_edit_country($country_id = '')

    { 

        if (!Helper::has_permission('countries')) {

            abort(403,'Access denied');

        }



        $country = [];

        if($country_id){

            $country = DB::table('country')->whereId($country_id)->first();

        }

        $switch = DB::table('switch_template')->select('id', 'currency')->get();

        return view('settings.country', compact('country', 'switch'));

    }



    /**

    * Update credits.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function save_country(Request $request)

    { 

        if (!Helper::has_permission('countries', 'edit')) {

            abort(403,'Access denied');

        }



        $validator = Validator::make($request->all(), ['country_name'=>'required|max:30', 'country_code'=>'required|max:5', 'short_code'=>'required|max:5', 'dial_code'=>'required|max:5', 'currency'=>'required|max:5', 'currency_symbol'=>'required']);

        

        if ($validator->fails()){

            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);

        }



        $data =['country_name'=>$request->country_name, 'country_code'=>$request->country_code, 'short_code'=>$request->short_code, 'dial_code'=>$request->dial_code, 'otp_type'=>$request->otp_type, 'switch_id'=>$request->switch_id, 'access_number'=>$request->access_number, 'time_zone'=>$request->time_zone, 'land_price'=>($request->land_price)?:'-', 'mob_price'=>($request->mob_price)?:'-', 'currency'=>$request->currency, 'currency_symbol'=>$request->currency_symbol, 'popular'=>$request->popular, 'accessnumber_support'=>$request->access_support, 'callback_support'=>$request->callback_support, 'wifi_support'=>$request->wifi_support, 'conference_support'=>$request->conference_support, 'tax'=>$request->tax, 'status'=>$request->status];



        if($request->country_id){

            $country = DB::table('country')->where('id', $request->country_id)->update($data);

            $message = 'Country details updated successfully';

        }else{

            $country = DB::table('country')->insertGetId($data);

            $message = 'Country created successfully';

        }

        // $rates = DB::table('tbl_rates')->where(['country_id' => $id, 'rate_type' => 1])->first();

        // DB::table('tbl_rates')->where(['country_id' => $id, 'rate_type' => 1])->update(['app_price' => $request->land_price]);

        // $rates = DB::table('tbl_rates')

        //             ->where(['country_id' => $id, 'rate_type' => 2])->first();

        // DB::table('tbl_rates')->where('id',$rates->id)->update(['app_price' => $request->mob_price]);



        if($country)

            return redirect()->back()->with(['message' => $message]);

        else

            return redirect()->back()->withErrors(['Faild to upate country details']);

    }



    /**

    * List the discount coupons

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function list_coupons()

    { 

        if (!Helper::has_permission('coupons')) {

            abort(403,'Access denied');

        }

        $coupons = DiscountCoupon::get();

        return view('settings.coupon-list', compact('coupons'));

    }



    /**

    * Edit discount coupon

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function manage_coupon(Request $request)

    { 

        $discount_coupon = true;

        if(isset($request->coupon_id)){

            if (!Helper::has_permission('coupons', 'edit')){

                return response()->json(['error' => true, 'message' => 'Access denied']);

            }

            $coupon = DB::table('discount_coupons')->whereId($request->coupon_id)->first();

            $html = view('modal-popup', compact('discount_coupon','coupon'))->render();

        }else{

            if (!Helper::has_permission('coupons', 'create')){

                return response()->json(['error' => true, 'message' => 'Access denied']);

            }

            $html = view('modal-popup', compact('discount_coupon'))->render();

        }

        return response()->json(['error' => false, 'html' => $html]);

    }



    

    

    /**

    * Save discount coupon.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function save_coupon(Request $request)

    {                    

        parse_str($request->coupon, $coupon);

        $today = Carbon::now()->format('Y-m-d');

        if($today > $coupon['expiry_date']) {

            return response()->json(['error' => true, 'message' => 'Expiry date invalid']);

        }

        

        $data = ['coupon_code' => $coupon['coupon_code'], 'discount_value' => $coupon['discount_value'], 'is_fixed' => $coupon['is_fixed'], 'expiry_date' => $coupon['expiry_date'], 'status' => $coupon['status']];

        if(isset($coupon['coupon_id'])) {

            if (!Helper::has_permission('coupons', 'delete')) {

                return response()->json(['error' => true, 'message' => 'Access denied']);

            }



            $result = DiscountCoupon::where('id',$coupon['coupon_id'])->update($data);

            if (!$result) {

                return response()->json(['error' => true, 'message' => 'Invalid Discount Coupon']);

            }

            return response()->json(['error' => false, 'message' => 'Coupon updated successfully']);

        } else {

            if (!Helper::has_permission('coupons', 'create')){

                return response()->json(['error' => true, 'message' => 'Access denied']);

            }

            $result = DiscountCoupon::create($data);

            return response()->json(['error' => false, 'message' => 'Coupon created successfully']);

        }       

    }



    /**

    * Edit discount coupon

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function delete_coupon(Request $request)

    {

        if (!Helper::has_permission('coupons', 'delete')){

            return response()->json(['error' => true, 'message' => 'Access denied']);

        }



        DiscountCoupon::whereId($request->coupon_id)->delete();

        return response()->json(['error' => false]);

    }



    /**

    * Show the Email Template.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function template($id = '')

    {

        if (!Helper::has_permission('settings')) {

            abort(403,'Access denied');

        }
        $content  = [];
        $list     = [];
        $template_html = "";
        if($id){
            $id       = Crypt::decrypt($id);
            $template = DB::table('email_template')->whereId($id)->first();
            if(!empty($template)){
                $content = $template;
                $template_html = $template->email_content;
            }
        }
        $template = DB::table('email_template')->select('id','email_name')->get();

        if($template->isNotEmpty()){
            $list = $template;
        }
        return view('settings.email-template',compact('list','content','id','template_html'));
    }
    /**

    * add and update the Email Template.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function email_template_actions(Request $request)
    {
        $rules = array('tempname' => 'required','emailcontent' => 'required');
        $messages = array(
        'tempname.required' => 'Please provide template name',
        'emailcontent.required' => 'Please provide email content',
        );
        $validator = Validator::make($request->all(), $rules,$messages);

        // Validate the input and return correct response
        if ($validator->fails()){
          return response()->json(['status'=>422,'msg'=>$validator->errors()->all()]);
        }else{
            $editid         = $request->editid;
            $tempname       = $request->tempname;
            $emailcontent   = $request->emailcontent;
            $data           = [
                                'email_name'=>$tempname,
                                'email_content'=>$emailcontent
                            ];
            if($editid != ""){
                $editid = Crypt::decrypt($editid);
                $update = DB::table('email_template')->where(['id'=>$editid])->update($data); 
                return response()->json(['status'=>200,'msg'=>'Template updated..']);  
            
            }else{
                $insert = DB::table('email_template')->insert($data); 
                if($insert){
                    return response()->json(['status'=>200,'msg'=>'New Template added..']);  
                }else{
                    return response()->json(['status'=>422,'msg'=>['Technical error..']]);
                }
            }
        }
    }
     /**

    * delete the Email Template.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function email_template_delete(Request $request)
    {
        $tempid = $request->tempid;
        if($tempid != ""){
            $tempid = Crypt::decrypt($tempid);
            $delete = DB::table('email_template')->whereId($tempid)->delete();
            if($delete){
               return response()->json(['status'=>200,'msg'=>'Template deleted..']); 
            }else{
                return response()->json(['status'=>422,'msg'=>'Technical error. Unable to delete template']); 
            }
        }
    }
    /**

    * Function activity log page.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function activity_log(Request $request)

    {   
        $adminlist = [];
        $userslist = [];

        $admins     = Admins::find(Auth::id());
        $role       = $admins->roles->short_code;

        $admin = DB::table('admins')
                    ->select('id',DB::raw('concat(first_name," ",last_name) as name'))
                    ->orderBy('first_name','ASC');
                    
        if($role == 'DEALER'){
            $admin  = $admin->where('id', Auth::id())
                            ->orWhere('parent_id', Auth::id());
        }
        $admin = $admin->get();
        // $users = DB::table('users')
        //             ->select('id','name')
        //             ->where('name','<>',"")
        //             ->orderBy('first_name','ASC')
        //             ->get();
        if($admin->isNotEmpty()){
            $adminlist =  $admin;
        }
        // if($users->isNotEmpty()){
        //     $userslist =  $users;
        // }
        return view('settings.activity-log',compact('adminlist','userslist'));

    }
    /**
    * Show the application activity log list.
    *

    * @return \Illuminate\Contracts\Support\Renderable

    */
    public function list_activitylog(Request $request){

        $admins     = Admins::find(Auth::id());
        $role       = $admins->roles->short_code;

        $querydatas = DB::table('activity_log as al')
                    ->select('al.id as actid','al.description',DB::raw('concat(ad.first_name," ",ad.last_name) as adminname'),DB::raw('concat(us.first_name," ",us.last_name) as username'),'al.created_at')
                    ->leftJoin('admins as ad', 'ad.id', '=', 'al.admin_id')
                    ->leftJoin('users as us', 'us.id', '=', 'al.user_id')
                    ->orderBy('al.created_at','DESC');

        if($role == 'DEALER'){
            $querydatas  = $querydatas->where('ad.id', Auth::id())
                            ->orWhere('ad.parent_id', Auth::id());
        }

        if ($request->has('adminid') && $request->get('adminid') != "") {
                $querydatas->where('al.admin_id',Crypt::decrypt($request->get('adminid')));
        }
        if ($request->has('userid') && $request->get('userid') != "") {
                $querydatas->where('al.user_id',$request->get('userid'));
        }
        if($request->from_date){
            $querydatas->whereDate('al.created_at','>=', $request->from_date);
        }
        if($request->to_date){
            $querydatas->whereDate('al.created_at','<=', $request->to_date);
        }           
        $result = Datatables::of($querydatas)
                    ->editColumn('created_at', function ($date) {
                     return $date->created_at ? with(new Carbon($date->created_at))->format('d-m-Y') : '';
                    })
                    ->addColumn('name', function ($data) {
                        return ($data->adminname !="") ? $data->adminname : $data->username;
                    })
                    ->make(true);
        return $result;
    }


    /**

    * Show the Payment Gateway.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function payment_gateway()

    {

        if (!Helper::has_permission('settings')) {

            abort(403,'Access denied');

        }

                

        return view('settings.template');

    }

    

    /**

    * Show the credit denomination.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function list_credits()

    {

        if (!Helper::has_permission('settings')) {

            abort(403,'Access denied');

        }

                

        return view('settings.template');

    }

    

    /**

    * Show the credit denomination.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function switch_template()

    {

        if (!Helper::has_permission('settings')) {

            abort(403,'Access denied');

        }

                

        return view('settings.template');

    }



    /**

    * Show the did pool numbers.

    *

    * @return \Illuminate\Contracts\Support\Renderable

    */

    public function list_did_numbers()

    { 

        if (!Helper::has_permission('did_pool')) {

            abort(403,'Access denied');

        }



        $dids = DB::table('didlist')->get();

        return view('settings.did-pool', compact('dids'));

    }







    // /**

    // * Function profile page.

    // *

    // * @return \Illuminate\Contracts\Support\Renderable

    // */

    // public function myaccount(Request $request)

    // { 

    //     return view('settings.myaccount');

    // }

    // /**

    // * Function Change password.     

    // * @return Account Settings Page with Status

    // */

    // public function change_password(Request $request)

    // {

    //     $user_id            = Auth::user()->id;

    //     $password           = $request->password;

    //     $confirm_password   = $request->confirm_password;

    //     if($password == $confirm_password){

    //         $hashed_pswd = Hash::make($password);

    //         Admins::where('id', $user_id)

    //             ->update(['password' => $hashed_pswd]);

    //         return redirect('my-account')->with('message','Password changed successfully');

    //     }else{

    //         return redirect('my-account')->with('error','Password Not matching!');

    //     }

    // }

    // /**

    // * Function Notification page.

    // *

    // * @return \Illuminate\Contracts\Support\Renderable

    // */

    // public function notification_log(Request $request)

    // {         

    //     return view('settings.notification');

    // }

    // /**

    // * Function Notification page.

    // *

    // * @return \Illuminate\Contracts\Support\Renderable

    // */

    // public function list_notification_log(Request $request)

    // { 

    //     $notifi = DB::table('notification_log as nl')->select('nl.*','usr.name','usr.email','usr.phone')->join('users as usr','usr.id','=','user_id')->orderBy('nl.created_at','desc');

    //     return $result = DataTables::of($notifi)

    //             ->editColumn('status', function ($user) {

    //                 $stat = "";

    //                 switch ($user->status) {

    //                     case 0:

    //                         $stat = "Pending";

    //                         break;

    //                     case 1:

    //                         $stat = "Completed";

    //                         break;

    //                     default:

    //                         $stat = "";

    //                         break;

    //                 }

    //             return $stat;

    //             })

    //             ->editColumn('created_at', function ($user) {

    //             return $user->created_at ? with(new Carbon($user->created_at))->format('d-m-Y') : '';

    //             })

    //             ->addColumn('action', function ($user) {

    //             return ($user->status == 0 && ($user->message =='Auto Subscription EE Subscription Renewal Error' || $user->message == 'Manual Subscription EE Subscription Renewal Failed')) ? '<button class="update_ee_renew" data-not_id="'.$user->id.'">Update</button>':'';

    //             })

    //             ->make(true);

    // }



    // /**

    // * Function Notification page.

    // *

    // * @return \Illuminate\Contracts\Support\Renderable

    // */

    // public function notification_manage(Request $request)

    // { 

    //     $notfic = DB::table('notification_log')->where('id', $request->not_id)->first();

    //     $description = explode(',', $notfic->description);

    //     $auto_id = explode(':', $description[0])[1];

    //     $plans = AutoPlan::where('id',$auto_id)->first();



    //     $where['user_id']       = $notfic->user_id; 

    //     $where['plan_type']     = $plans->plan_type;

    //     $update['status']       = 0; 

    //     UserPlan::where($where)->update($update);   

    //     $usage['plan_id']       = $plans->plan_id;  

    //     $usage['user_id']       = $notfic->user_id;

    //     $usage['payment_id']    = 0; 

    //     $usage['status']        = 1; 

    //     $usage['plan_type']     = $plans->plan_type;

    //     UserPlan::create($usage); 

    //     $next_renewal = Carbon::parse($notfic->created_at)->addDays(30)->format('Y-m-d');

    //     AutoPlan::where('id',$auto_id)->update(['next_renewal' => $next_renewal]); 

    //     DB::table('notification_log')->where('id', $request->not_id)->update(['status' => 1]);

    //     return 1;

    // }



    // *

    // * Function Change password.     

    // * @return Account Settings Page with Status

    

    // public function account_edit(Request $request)

    // {

    //     $user_id    = Auth::user()->id;

    //     $firstname       = $request->first_name;

    //     $lastname       = $request->last_name;

    //     $email      = $request->email;

    //     Admins::where('id', $user_id)

    //             ->update(['first_name' => $firstname,'last_name'=>$lastname,'email'=>$email]);

    //         return redirect('my-account')->with('message','Updated successfully');

    // }















    

}

