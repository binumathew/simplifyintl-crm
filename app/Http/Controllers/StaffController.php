<?php

namespace App\Http\Controllers;

use DB;
use Mail;
use Auth;
use Crypt;
use Helper;
use App\Models\Admins;
use App\Models\StaffCommission;
use App\Mail\StaffRegistration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
    * Display listing of the staff.
    *
    * @return \Illuminate\Http\Response
    */
    public function staff_list()
    {
        if (!Helper::has_permission('staff') && !Helper::has_permission('staff','view_own')) {
            abort(403,'Access denied');
        }

        $staff = DB::table('admins')->select('tbl_roles.name','admins.*')->join('tbl_roles','role','=','tbl_roles.id')
                        ->where('tbl_roles.name', '!=', 'DEALER');

        if(Helper::has_permission('staff')){
        }else if(Helper::has_permission('staff','view_own')){
            $staff = $staff->where('admins.id', Auth::id())
                        ->orWhere('parent_id', Auth::id());
        }                
        $staff = $staff->get();

        return view('staff-list', compact('staff'));
    }

    /**
    * Display listing of the staff.
    *
    * @return \Illuminate\Http\Response
    */
    public function dealers()
    {
        if (!Helper::has_permission('dealer') && !Helper::has_permission('dealer','view_own')) {
            abort(403,'Access denied');
        }

        $dealer = DB::table('admins')->select('tbl_roles.name','admins.*')->join('tbl_roles','role','=','tbl_roles.id')
                    ->where('tbl_roles.name', 'DEALER');
            
        if(Helper::has_permission('dealer')){
        }else if(Helper::has_permission('dealer','view_own')){
            $dealer = $dealer->where('admins.id', Auth::id())
                        ->orWhere('parent_id', Auth::id());
        }        
        $dealers = $dealer->get();
        $providers = DB::table('tbl_providers')->where('status',1)->get();

        return view('dealer-list', compact('dealers','providers'));
    }


    public function send_password(Request $request)
    {
        $admin_id = Crypt::decrypt($request->id);
        $admin = DB::table('admins')->where('id', $admin_id)->first();
        if($admin){
            $password = str_random(8);
            $hash = Hash::make($password);
            DB::table('admins')->where('id', $admin_id)->update(['password' => $hash]);

            $name= $admin->first_name.' '.$admin->last_name;
            $obj = (object)['name' =>  $name, 'subject' => 'Welcome to '.config('settings.app_name'),'heading' =>'Welcome To '.config('settings.app_name'), 'email' =>  $admin->email, 'password' => $password,'promocode' => $admin->promocode];
            Mail::to($admin->email)
                ->bcc('jijo.joseph@gencomtel.com')
                ->send(new StaffRegistration($obj));
            return response()->json(['error' => false, 'message' => 'Password successfully send to registerd email']);
        } else {
            return response()->json(['error' => true, 'message' => 'user details doesnot exist']);
        }
    }    

    /**
    * Show the form for creating a staff.
    *
    * @return \Illuminate\Http\Response
    */
    public function create_staff()
    {
        if (!Helper::has_permission('staff','create')) {
            abort(403,'Access denied');
        }

        $staff_type = 1;
        $roles = DB::table('tbl_roles')->get();
        $gateways = DB::table('payment_gateway')->where('status', 1)->get();
        return view('create-staff', compact('roles', 'gateways', 'staff_type'));
    }

    /**
    * Show the form for creating a dealer.
    *
    * @return \Illuminate\Http\Response
    */
    public function create_dealer()
    {
        if (!Helper::has_permission('dealer','create')) {
            abort(403,'Access denied');
        }

        $staff_type = 2;
        $gateways = DB::table('payment_gateway')->where('status', 1)->get();
        $roles = DB::table('tbl_roles')->where('short_code', 'DEALER')->get();
        return view('create-staff', compact('gateways', 'roles', 'staff_type'));
    }

    /**
    * Show the form for creating a dealer.
    *
    * @return \Illuminate\Http\Response
    */
    public function check_promocode(Request $request){
        if(Admins::where('promocode', $request->promocode)->exists()){ 
            return response()->json(['success' => false, 'message' => 'Promocode already exists!']);   
        }else{
            return response()->json(['success' => true, 'message' => 'Promocode available!']);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save_staff(Request $request)
    {
        if (!Helper::has_permission('staff','create') || !Helper::has_permission('dealer','create')) {
            abort(403,'Access denied');
        }

        if(isset($request->staff_id)){
            $admin = Admins::find($request->staff_id);
            $promo['success'] = true;
        }else{
            $admin = new Admins();  
            $password = str_random(8);
            $admin->password = Hash::make($password); 
            $admin->promocode = strtoupper($request->promocode); 
            $promo = $this->check_promocode($request); //check promocode
            $promo = $promo->getOriginalContent();
        }
        
        $validatedData = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
        ]);
        $admin->first_name = $request->first_name;
        $admin->last_name = $request->last_name;
        $admin->email = $request->email;
        $admin->phone = $request->phone;        
        $admin->role = $request->role;        
        $admin->parent_id = ($request->parent)? $request->parent:0; 
        $payment_mode = ($request->payment_mode)?$request->payment_mode:[];
        $admin->payment_mode = json_encode($payment_mode); 
        $admin->status = $request->status;        
        if($promo['success']){
            if ($admin->save()) {
                if(!isset($request->staff_id)){
                    $name= ($request->first_name)? $request->first_name.' '.$request->last_name:'Staff';
                    $obj = (object)['name' =>  $name, 'subject' => 'Welcome to '.config('settings.app_name'),'heading' =>'Welcome To '.config('settings.app_name'), 'email' =>  $request->email, 'password' => $password,'promocode' => strtoupper($request->promocode)];
                    $bcc_emails = Helper::get_option('bcc_emails');
                    $bcc_emails = explode(',', $bcc_emails);
                    Mail::to($request->email)
                        ->bcc($bcc_emails)
                        ->send(new StaffRegistration($obj)); 
                }
                if($admin->roles->short_code == 'DEALER'){
                    return response()->json(['success' => true, 'message' => 'Dealer has been created successfully', 'url' => '/dealers']);                    
                } else {
                    return response()->json(['success' => true, 'message' => 'Staff has been created successfully', 'url' => '/staff-list']);                    
                }                     
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to create the user']);
            }
        }else{
            return response()->json(['success' => false, 'message' => $promo['message']]);
        }
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit_staff_details($id)
    {
        if (!Helper::has_permission('staff','edit')){
            abort(403,'Access denied');              
        }

        $id = Crypt::decrypt($id);
        $staff = Admins::where('id', $id)->first();
        if (!$staff) {
            abort(404,'User doesnot exist.');
        }

        $staff_type = 1;
        $roles = DB::table('tbl_roles')->get();
        $gateways = DB::table('payment_gateway')->where('status', 1)->get();
        $parents = DB::table('admins')->select('id', DB::raw('CONCAT(first_name," ", last_name) as full_name'))->where('role', $staff->role)->get();
        return view('create-staff', compact('staff', 'roles', 'staff_type', 'gateways','parents'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit_dealer_details($id)
    {
        if(!Helper::has_permission('dealer','edit')) {
            abort(403,'Access denied');    
        }

        $id = Crypt::decrypt($id);
        $staff = Admins::where('id', $id)->first();
        if (!$staff) {
            abort(404,'User doesnot exist.');
        }
        $staff_type = 2;
        $roles = DB::table('tbl_roles')->where('short_code', 'DEALER')->get();
        $gateways = DB::table('payment_gateway')->where('status', 1)->get();
        $parents = DB::table('admins')->select('id', DB::raw('CONCAT(first_name," ", last_name) as full_name'))->where('role', $staff->role)->get();
        return view('create-staff', compact('staff', 'roles', 'staff_type','gateways','parents'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Helper::has_permission('staff','edit') || !Helper::has_permission('dealer','edit')) {
            abort(403,'Access denied');
        }
        $admin = Admins::where('id', $id)->first();
        if (!$admin) {
            abort(404,'User doesnot exist.');
        }
        $validatedData = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
        ]);
        // $password = '';

        if($id == $request->parent) {
            abort(403,'User cannot be it\'s own parent');
        }
        
        $admin->first_name = $request->first_name;
        $admin->last_name = $request->last_name;
        $admin->email = $request->email;
        $admin->phone = $request->phone;
        $admin->role = $request->role;
        $admin->parent_id = $request->parent;
        // $admin->password = Hash::make($password);
        $admin->payment_mode = json_encode($request->payment_mode); 
        $admin->status = $request->status;

        if ($admin->promocode == '') {
            $admin->promocode = $request->promocode;
        }
        if ($admin->save()) {
            if($admin->roles->short_code == 'DEALER'){
                return redirect('/dealers')->with('success', 'Dealer details updated successfully');
            } else {
                return redirect('/staff')->with('success', 'Staff details updated successfully');
            }             
        } else {
            abort(403,'Failed to add new staff');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        Admins::where('id', $id)->delete();
        return redirect()->back();
    }

     /**
    * Get the list of parents
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function get_parents(Request $request)
    {
        $role = $request->role;   
        $parents = DB::table('admins')->select('id', DB::raw('CONCAT(first_name," ", last_name) as full_name'))->where('role', $role)->get();
        return $parents;
    }


}
