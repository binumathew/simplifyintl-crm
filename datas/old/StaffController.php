<?php

namespace App\Http\Controllers;

use DB;
use Mail;
use Auth;
use Crypt;
use Helper;
use DataTables;
use App\Models\Admins;
use App\Models\StaffCommission;
use Illuminate\Http\Request;
use App\Mail\StaffRegistration;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Helper::has_permission('staff')) {
            abort(403,'Access denied');
        }
        $staff_type = 1;
        $staff = DB::table('admins')->select('tbl_roles.name','admins.*')->join('tbl_roles','role','=','tbl_roles.id')->where('tbl_roles.name', '!=', 'DEALER')->get();
        return view('staff.index', compact('staff','staff_type'));
    }

    public function dealers()
    {
        if (!Helper::has_permission('dealer')) {
            abort(403,'Access denied');
        }
        $staff_type = 2;
        $dealer = DB::table('admins')->select('tbl_roles.name','admins.*')->join('tbl_roles','role','=','tbl_roles.id')->where('tbl_roles.name', 'DEALER');
            if(Auth::user()->role == 5){
                $dealer = $dealer->where('admins.id',Auth::id())
                            ->orWhere('parent_id',Auth::id());
            }
        $dealer = $dealer->get();
        return view('staff.dealer', compact('dealer','staff_type'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Helper::has_permission('staff','create')) {
            abort(403,'Access denied');
        }
        $staff_type = 1;
        $roles = DB::table('tbl_roles')->get();
        return view('staff.create', compact('roles','staff_type'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_dealer()
    {
        if (!Helper::has_permission('dealer','create')) {
            abort(403,'Access denied');
        }
        $staff_type = 2;
        $roles = DB::table('tbl_roles')->where('short_code', 'DEALER')->get();
        return view('staff.create', compact('roles','staff_type'));
    }

    public function check_promocode(Request $request){
        $promocode  = $request->promocode;
        $existing   = Admins::where('promocode', $promocode)->count(); //check promocode unique
        $promo      = new \stdClass();
        if($existing === 0 && strlen($promocode) <= 15){
            $promo->avail = true;
            $promo->msg   = "Promocode available!!";
        }else{
            $promo->avail = false;
            $promo->msg   = "Promocode already exists!!";
        }
        return json_encode($promo);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Helper::has_permission('staff','create') || !Helper::has_permission('dealer','create')) {
            abort(403,'Access denied');
        }
        $admin = new Admins();
        $validatedData = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
        ]);

        $password = str_random(8);
        $admin->first_name = $request->first_name;
        $admin->last_name = $request->last_name;
        $admin->email = $request->email;
        $admin->phone = $request->phone;
        $admin->password = Hash::make($password);
        $admin->role = $request->role;
        $admin->promocode = $request->promocode;
        $admin->parent_id = ($request->parent)? $request->parent:0; 
        $admin->payment_mode = json_encode($request->payment_mode); 
        $admin->status = $request->status;
        $exist = json_decode($this->check_promocode($request)); //check promocode
        if($exist->avail ){
            if ($admin->save()) {
                $name= ($request->first_name)? $request->first_name.' '.$request->last_name:'Staff';
                $obj = (object)['name' =>  $name, 'subject' => 'Welcome to Avoomobile','heading' =>'Welcome To Avoomobile', 'email' =>  $request->email, 'password' => $password,'promocode' => $request->promocode];
                Mail::to($request->email)
                    ->bcc('jijo.joseph@gencomtel.com')
                    ->send(new StaffRegistration($obj)); 
                if($admin->role == 5){
                    return redirect('/dealers')->with('success', 'New Dealer has been created successfully');
                } else {
                    return redirect('/staff')->with('success', 'New Staff has been created successfully');
                }                     
            } else {
                abort(403,'Failed to add new staff');
            }
        }else{
            abort(403,$exist->msg);
        }
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //Auth::loginUsingId();
        if (!Helper::has_permission('staff','edit') || !Helper::has_permission('dealer','edit')) {
            abort(403,'Access denied');
        }
        $user = Admins::where('id', $id)->first();
        if (!$user) {
            abort(404,'User doesnot exist.');
        }

        $roles = DB::table('tbl_roles')->get();
        return view('staff.edit', compact('user', 'id', 'roles'));
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
            if($admin->role == 5){
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
        $code = DB::table('tbl_roles')->where('id', $role)->value('short_code');

        $parents  = DB::table('admins')->select(['id','first_name', 'last_name'])
            ->whereIn('role', function($query) use ($code) {
            $query->select('id')
            ->from('tbl_roles')
            ->where('short_code', $code);
        })->get();

        // $parents = DB::table('admins')->select(['id','first_name', 'last_name'])
            // ->where('role', $role)
            // ->get();
        return json_encode($parents);
    }

    /**
     * List staff commission
     *
     * @return \Illuminate\Http\Response
     */
    public function staff_commission()
    {
        if (!Helper::has_permission('staff')) {
            abort(403,'Access denied');
        }
        $roles = DB::table('tbl_roles')->get();
        return view('staff.staff-commission', compact('roles'));
    }

    public function staff_commission_list(Request $request)
    {
        if (!Helper::has_permission('staff')) {
            abort(403,'Access denied');
        }

        $role_commission = DB::table('tbl_roles as r')
            ->join('staff_commission as sc', 'sc.role_id', '=', 'r.id');

        if($request->role && !empty($request->role)) {
            $role_commission->where('sc.role_id', $request->role);
        }

        $role_commission = $role_commission->get();

        return Datatables::of($role_commission)->make(true);
    }

    /**
    * Interface to add staff commission
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function add_staff_commission()
    { 
        if (!Helper::has_permission('staff')) {
            abort(403,'Access denied');
        }

        $roles = DB::table('tbl_roles')->get();

        return view('add-staff-commission', compact('roles'));
    }

    /**
    * Edit staff commission
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function edit_staff_commission($id)
    { 
        if (!Helper::has_permission('staff')) {
            abort(403,'Access denied');
        }

        $commission_id = $id;

        $commission = DB::table('staff_commission')->whereId($commission_id)->first();
        $roles = DB::table('tbl_roles')->get();

        return view('edit-staff-commission', compact('commission', 'roles'));
    }

    /**
    * Save staff commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_staff_commission(Request $request)
    { 
        if (!Helper::has_permission('staff')) {
            abort(403,'Access denied');
        }

        if(isset($request->commission_id)) {
            $commission = StaffCommission::where('id',$request->commission_id)->first();
            if (!$commission) {
                abort(403, 'Invalid Staff Commission');
            }
        }
        else {
            $commission = new StaffCommission();
        }       


        DB::beginTransaction();

        try {

            $commission->role_id = $request->role;
            $commission->target_from = $request->target_from;
            $commission->target_to = $request->target_to;
            $commission->commission = $request->commission_rate;

            $commission->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            abort(403, $e->getMessage());
        }
        return redirect('/staff-commission');
    }
}
