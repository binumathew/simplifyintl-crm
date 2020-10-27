<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Helpers\DwpHelper;

use App\Models\User;
use App\Models\AutoPlan;

class AffinityController extends Controller
{
    /**
    * Soap WSDL Login
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function index(Request $request){
    	// $userlist = DB::table('users as us')
    	// 			->select('us.id as user_id','us.first_name','us.last_name','tss.phone_number','ap.id as autoplanid')
    	// 			->join('tbl_sim_stock as tss', 'tss.id', '=', 'us.stock_id')
    	// 			->join('auto_plan as ap', 'ap.user_id', '=', 'us.id')
    	// 			->whereIn('tss.provider',array('VUK','O2','EE_O2'))
    	// 			->where('ap.status',1)
    	// 			->get();

    	// if($userlist->isNotEmpty()){
    	// 	$client       = DwpHelper::initiate_soap_client();
    	// 	foreach ($userlist as $ukey => $ulist) {
    	// 		$autoplan = AutoPlan::find($ulist->autoplanid); 
    	// 		$provider = $autoplan->plan->provider;
    	// 		if($autoplan->user_list != ""){
	    // 			$userall     = User::find($autoplan->user_list);
	    // 			if(strpos($userall->userDetail->sim_account_id, 'AVCO2') !== false || strpos($userall->userDetail->sim_account_id, 'AVCVUK') !== false){
	    // 				$createuser = DwpHelper::create_new_site($client,$userall);
	    // 				if($createuser->status == 200){
	    // 				    $siteId = $createuser->siteId;
	    // 					DB::table('user_data')->where('user_id',$userall->id)->update(['sim_account_id'=>$siteId]);
	    // 					$addcli = DwpHelper::add_cli($client,$siteId,$userall);
	    // 					if($addcli->status == 200){
	    // 						DB::table('trusted_numbers')->where('trusted_number',$userall->phone)->update(['cli_id'=>$addcli->newCli]);
	    // 					}
	    // 				}else{
	    // 					print_r($createuser->error);
	    // 					print_r($userall);
	    // 				}
	    // 				//print_r();

	    // 			}
	    // 		}	
    	// 	}
    	// }
    }
    public function update_cli(Request $request){
        // $client    = DwpHelper::initiate_soap_client();
        // $get = DB::table('trusted_numbers as tn')
        //         ->select('tn.trusted_number','tn.cli_id','ud.site_id','tn.id as trustid')
        //         ->join('user_data as ud','ud.user_id','=','tn.user_id')
        //         ->where('cli_id','<>',0)->get();

        // foreach ($get as $gkey => $glist) {
        //     if(in_array($glist->trustid, [1,25,56])){
        //         continue;
        //     }
        //     $newnum = '0'.ltrim($glist->trusted_number,'+44');
        //     $addcli = DwpHelper::add_cli($client,$glist->site_id,$newnum);
        //     if($addcli->status == 200){
        //         DB::table('trusted_numbers')->whereId($glist->trustid)->update(['cli_id'=>$addcli->newCli]);
        //     }else{
        //         print_r('---error');
        //         print_r($glist);
        //         print_r('---end--');
        //     }
        //   print_r($addcli);
        // }
    }
    public function test_soap(Request $request){
        // $client    = DwpHelper::initiate_soap_client();
        // print_r($client);
        // print_r("hii");
        // die();
    }
}
