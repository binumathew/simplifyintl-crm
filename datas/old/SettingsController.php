<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Hash;
use DataTables;
use Carbon;
use App\Models\AutoPlan;
use App\Models\UserPlan;
use App\Models\Admins;
use App\Models\ScheduledTask;
use App\Jobs\CronTaskJob;

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
    * Function profile page.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function myaccount(Request $request)
    { 
        return view('settings.myaccount');
    }
    /**
    * Function Change password.     
    * @return Account Settings Page with Status
    */
    public function change_password(Request $request)
    {
        $user_id            = Auth::user()->id;
        $password           = $request->password;
        $confirm_password   = $request->confirm_password;
        if($password == $confirm_password){
            $hashed_pswd = Hash::make($password);
            Admins::where('id', $user_id)
                ->update(['password' => $hashed_pswd]);
            return redirect('my-account')->with('message','Password changed successfully');
        }else{
            return redirect('my-account')->with('error','Password Not matching!');
        }
    }
    /**
    * Function Notification page.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function notification_log(Request $request)
    {         
        return view('settings.notification');
    }
    /**
    * Function Notification page.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_notification_log(Request $request)
    { 
        $notifi = DB::table('notification_log as nl')->select('nl.*','usr.name','usr.email','usr.phone')->join('users as usr','usr.id','=','user_id')->orderBy('nl.created_at','desc');
        return $result = DataTables::of($notifi)
                ->editColumn('status', function ($user) {
                    $stat = "";
                    switch ($user->status) {
                        case 0:
                            $stat = "Pending";
                            break;
                        case 1:
                            $stat = "Completed";
                            break;
                        default:
                            $stat = "";
                            break;
                    }
                return $stat;
                })
                ->editColumn('created_at', function ($user) {
                return $user->created_at ? with(new Carbon($user->created_at))->format('d-m-Y') : '';
                })
                ->addColumn('action', function ($user) {
                return ($user->status == 0 && ($user->message =='Auto Subscription EE Subscription Renewal Error' || $user->message == 'Manual Subscription EE Subscription Renewal Failed')) ? '<button class="update_ee_renew" data-not_id="'.$user->id.'">Update</button>':'';
                })
                ->make(true);
    }

    /**
    * Function Notification page.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function notification_manage(Request $request)
    { 
        $notfic = DB::table('notification_log')->where('id', $request->not_id)->first();
        $description = explode(',', $notfic->description);
        $auto_id = explode(':', $description[0])[1];
        $plans = AutoPlan::where('id',$auto_id)->first();

        $where['user_id']       = $notfic->user_id; 
        $where['plan_type']     = $plans->plan_type;
        $update['status']       = 0; 
        UserPlan::where($where)->update($update);   
        $usage['plan_id']       = $plans->plan_id;  
        $usage['user_id']       = $notfic->user_id;
        $usage['payment_id']    = 0; 
        $usage['status']        = 1; 
        $usage['plan_type']     = $plans->plan_type;
        UserPlan::create($usage); 
        $next_renewal = Carbon::parse($notfic->created_at)->addDays(30)->format('Y-m-d');
        AutoPlan::where('id',$auto_id)->update(['next_renewal' => $next_renewal]); 
        DB::table('notification_log')->where('id', $request->not_id)->update(['status' => 1]);
        return 1;
    }

    /**
    * Function Change password.     
    * @return Account Settings Page with Status
    */
    public function account_edit(Request $request)
    {
        $user_id    = Auth::user()->id;
        $firstname       = $request->first_name;
        $lastname       = $request->last_name;
        $email      = $request->email;
        Admins::where('id', $user_id)
                ->update(['first_name' => $firstname,'last_name'=>$lastname,'email'=>$email]);
            return redirect('my-account')->with('message','Updated successfully');
    }

    /**
    * Function List Scheduled Tasks.     
    * @return List tasks
    */
    public function scheduled_task()
    {
        $tasks = ScheduledTask::all();            
        return view('scheduled-tasks',compact('tasks'));
    }

    /**
    * Function Manage Scheduled Tasks.     
    * @return Manage tasks
    */
    public function manage_scheduled_task($id='')
    {   
        if($id){
            $task = ScheduledTask::where('id', $id)->first();
            return view('scheduled-task',compact('task'))->render();
        } else {
            return view('scheduled-task',compact('task'))->render();
        }
    }

    /**
    * Function Save Scheduled Tasks.     
    * @return Save tasks
    */
    public function save_scheduled_task(Request $request)
    {        
        ScheduledTask::updateOrCreate(['id'=>$request->id],['description'=>$request->description,'command'=>$request->command,'status'=>$request->status]);
        if($request->id)
            $message = 'Updated successfully';
        else 
            $message = 'Added successfully';
        return redirect('scheduled-tasks')->with('message',$message); 
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
    * Function Notification page.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function api_logger(Request $request)
    {         
        return view('settings.api_log');
    }

    /**
    * Function Notification page.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_api_log(Request $request)
    { 
        $log = DB::table('api_log as al')->select('al.*','usr.name')->leftJoin('users as usr','usr.id','=','user_id')->orderBy('al.created_at','desc');
        return $result = DataTables::of($log)->make(true);
        
                // ->editColumn('status', function ($user) {
                //     $stat = "";
                //     switch ($user->status) {
                //         case 0:
                //             $stat = "Pending";
                //             break;
                //         case 1:
                //             $stat = "Completed";
                //             break;
                //         default:
                //             $stat = "";
                //             break;
                //     }
                // return $stat;
                // })
                // ->editColumn('created_at', function ($user) {
                // return $user->created_at ? with(new Carbon($user->created_at))->format('d-m-Y') : '';
                // })
                // ->make(true);
    }
}
