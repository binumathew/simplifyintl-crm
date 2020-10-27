<?php

namespace App\Http\Controllers;

use DB;
use Helper;
use Auth;
use DataTables;
use App\Models\User as ModalUser;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
    * Show the Sim plans.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_plans()
    { 
        if (!Helper::has_permission('plan_management')) {
            abort(403,'Access denied');
        }
        $plans = DB::table('tbl_plans')->get();
        return view('plan.list', compact('plans'));
    }

    /**
    * Creat Sim plan.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function create_plan()
    { 
        if (!Helper::has_permission('plan_management', 'create')) {
            abort(403,'Access denied');
        }

        return view('plan.create');
    }

    /**
    * Show the Sim plan details.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function edit_plan($id)
    { 
        if (!Helper::has_permission('plan_management', 'edit')) {
            abort(403,'Access denied');
        }
        $plan = DB::table('tbl_plans')->whereId($id)->first();
        return view('plan.edit', compact('plan'));
    }
    /**
    * Function Update Sim plans.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_plan(Request $request)
    { 
        if(isset($request->id)) {
            if (!Helper::has_permission('plan_management', 'edit')) {
                abort(403,'Access denied');
            }

            $data = $request->all();
            unset($data['_token']);

            $response = DB::table('tbl_plans')->whereId($request->id)->update($data);  
            if($response) {
                return redirect()->back()->with('message', 'Plan update successfully!');
            } else {
                return redirect()->back()->with('error', 'Failed to update this plan, please try again');
            }
        } else {
            if (!Helper::has_permission('plan_management')) {
                abort(403,'Access denied');
            }

            $data = $request->all();
            unset($data['_token']);
            $id = DB::table('tbl_plans')->insertGetId($data);
            if($id)
                return redirect('/edit-plan/'.$id)->with('message','Plan Created successfully!');
            else
                return redirect()->back()->with('error', 'Failed to create this plan, please try again');
        }        
    }

    /**
    * Show the bundle packages.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_packages()
    {
    if (!Helper::has_permission('package_management')) {
            abort(403,'Access denied');
        } 
        $packages = DB::table('tbl_bundles')->get();
        return view('plan.package', compact('packages'));
    }

    /**
    * Creat Sim Package.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function create_package()
    { 
        if (!Helper::has_permission('package_management', 'create')) {
            abort(403,'Access denied');
        }

        $plans = DB::table('tbl_plans')->get();
        return view('plan.create_package', compact('plans'));
    }

    /**
    * Show the Sim plan details.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function edit_package($id)
    { 
        if (!Helper::has_permission('package_management', 'edit')) {
            abort(403,'Access denied');
        }
        $package = DB::table('tbl_bundles')->whereId($id)->first();
        $plans = DB::table('tbl_plans')->select('id','plan_name')->where('status', 1)->get();
        return view('plan.edit_package', compact('package','plans'));
    }
    /**
    * Function Update Sim plans.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_package(Request $request)
    { 
        if(isset($request->id)) {
            if (!Helper::has_permission('package_management', 'edit')) {
                abort(403,'Access denied');
            }

            $data = $request->all();
            unset($data['_token']);

            $response = DB::table('tbl_bundles')->whereId($request->id)->update($data);  
            if($response) {
                return redirect()->back()->with('message', 'Package update successfully!');
            } else {
                return redirect()->back()->with('error', 'Failed to update this package, please try again');
            }
        } else {
            if (!Helper::has_permission('package_management')) {
                abort(403,'Access denied');
            }

            $data = $request->all();
            unset($data['_token']);
            $id = DB::table('tbl_bundles')->insertGetId($data);
            if($id)
                return redirect('/edit-package/'.$id)->with('message','Package Created successfully!');
            else
                return redirect()->back()->with('error', 'Failed to create this package, please try again');
        }        
    }

    /**
    * Show the Switch plan template .
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_switch_template()
    { 
        if (!Helper::has_permission('switch_management')) {
            abort(403,'Access denied');
        }
        $switch = DB::table('switch_template')->get();
        return view('switch-management', compact('switch'));
    }
    /**
    * Show the Plan.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function plan_purchase($user='')
    {  
        // if (!Helper::has_permission('plan_purchase')) {
        //     abort(403,'Access denied');
        // }

        $plans = DB::table('tbl_plans')->where('status', 1)->whereIn('category',[1,2,3])->get();
        $packages = DB::table('tbl_bundles')->where(['status'=> 1,'category'=>1])->get();
        $parking = DB::table('tbl_plans')->where('id',16)->first();


        /* $role     = DB::table('tbl_roles')->select('short_code')
                    ->whereId(Auth::user()->role)->first()->short_code;
        if($role == 'ADMIN' || $role == 'MANAGER'){
           $plans    = $plans->get(); 
           $packages = $packages->get(); 
        }else{
           $plans    = $plans->where(function($q) {
                                $q->where('category', 1)
                                ->orWhere('category', 2);
                            })->get(); 
           $packages = $packages->where(function($q) {
                                $q->where('category', 1)
                                ->orWhere('category', 2);
                            })->get();  
        }*/
        return view('plan.planlist',compact('plans','packages','parking','user'));
    }
}
