<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\AttHelper;

use Helper;

class AttController extends Controller
{
    /**
    * ATT .     
    * @return verify imei
    */
    public function verify_imei(Request $request)
    {
    	$mvno_key = Helper::get_option('att_mvno_key');
    	$imei     = '357751083000000';
    	$url 	= 'devices/'.$imei.'?MVNO='.$mvno_key;

    	$result = AttHelper::call_api_request($url); 
    	if($result->status == 200){
    		print_r($result);
    	}else{
    		print_r("error");
    	}  	
    	
    }
    /** 
    * ATT .     
    * @return Sim & Ported Number Activation
    */
    public function sim_activation(Request $request)
    {
    	$mvno_key   = Helper::get_option('att_mvno_key');
    	$url 		= 'activations?MVNO='.$mvno_key;

    	$data     = new \stdClass();
    	$address  = new \stdClass();
    	$charcter = new \stdClass();
    	$addressdata = new \stdClass();
    	$activate    = new \stdClass();

    	$phoneNumber = '';

    	$chardetails = [['name'=>'serviceZipCode','value'=>'07105'],['name'=>'planName','value'=>'Mobile select'],['name'=>'serviceType','value'=>'Data and Voice'],['name'=>'IMEI','value'=>'357751083000000'],['name'=>'ICCID','value'=>'89014104270225985273'],['name'=>'size','value'=>'1GB'],['name'=>'tethering','value'=>'Yes']];

    	if($phoneNumber != ""){
    		$charcter->phoneNumber = $phoneNumber;
    	}

    	$charcter->characteristics = $chardetails;

    	$firstName = "John";
    	$lastName  = "Smith";
    	$email     = "test@gmail.com";
    	$contactnum= "111111113";

    	$address->streetNumber = '123';
    	$address->streetDirection = '';
    	$address->streetName = 'Main Street';
    	$address->city = 'Newark';
    	$address->state = 'NJ';
    	$address->zipCode = '07105';

    	$addressdata->address 	= $address;
    	$addressdata->firstName = $firstName;
    	$addressdata->lastName 	= $lastName;
    	$addressdata->email    	= $email;
    	$addressdata->contactNumber = $contactnum;

    	$activate->subscriber = $addressdata;
    	$activate->service    = $charcter;

		$data->activations[]  = $activate;

		$result = AttHelper::call_api_request($url,json_encode($data),'POST'); 
    	if($result->status == 200){
    		print_r($result);
    	}else{
    		print_r("error");
    	}  
    }
    /**
    * ATT .     
    * @return sim activation status
    */
    public function check_sim_activation_status(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$activationId = '5f55e52cf48b42d04e3a2ab1';
    	$url 	= 'activations/'.$activationId.'?MVNO='.$mvno_key;

    	$result = AttHelper::call_api_request($url); 
    	if($result->status == 200){
    		print_r($result);
    	}else{
    		print_r("error");
    	}  	
    }
    /**
    * ATT .     
    * @return port eligible
    */
    public function check_port_eligible(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$phonenumber  = '6467976577';
    	$zipcode      = '33324';
    	$url 	= 'portin/eligible?phoneNumber='.$phonenumber.'&zipCode='.$zipcode.'&MVNO='.$mvno_key;
    	$result = AttHelper::call_api_request($url); 
    	if($result->status == 200){
    		print_r($result);
    	}else{
    		print_r("error");
    	}  
    }
    /**
    * ATT .     
    * @return sim port
    */
    public function sim_port(Request $request)
    {
    	$mvno_key   = Helper::get_option('att_mvno_key');
    	$url 		= 'portin?MVNO='.$mvno_key;

    	$address    = new \stdClass();
    	$port       = new \stdClass();
    	$addressdata = new \stdClass();
    	$oldserprovider = new \stdClass();
    	$serviceInfo    = new \stdClass();

    	$phoneNumber        = "6467976577";
    	$portRequestLineId  = "6467976577";
    	$serviceZipCode     = "33324";

    	$serviceInfo->area   = "008282000557";

    	$firstName = "Jane";
    	$lastName  = "Doe";

    	$address->streetNumber = '111';
    	$address->streetDirection = 'NW';
    	$address->streetName = '100';
    	$address->streetType = 'AVE';
    	$address->city = 'Plantation';
    	$address->state = 'FL';
    	$address->zipCode = '33324';

    	$oldserprovider->accountNumber = "7022537";
    	$oldserprovider->password      = "";
    	$oldserprovider->firstName     = $firstName;
    	$oldserprovider->lastName      = $lastName;

    	$addressdata->address 	= $address;
    	$addressdata->firstName = $firstName;
    	$addressdata->lastName 	= $lastName;

    	$port->phoneNumber 			= $phoneNumber;
    	$port->portRequestLineId 	= $portRequestLineId;
    	$port->serviceZipCode 		= $serviceZipCode;

    	$port->subscriber 			= $addressdata;
    	$port->authorizer 			= $addressdata;
    	$port->serviceInfo 			= $serviceInfo;
    	$port->oldServiceProvider 	= $oldserprovider;

    	$result = AttHelper::call_api_request($url,json_encode($port),'POST');

    	if($result->status == 200){
    		print_r($result);
    	}else{
    		print_r("error");
    	}  
    }
    /**
    * ATT .     
    * @return sim porting status
    */
    public function check_sim_porting_status(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$phoneNumber = "6467976577";
    	$url 	= 'portin/'.$phoneNumber.'?MVNO='.$mvno_key;

    	$result = AttHelper::call_api_request($url); 

    	if($result->status == 200){
    		print_r($result);
    	}else{
    		print_r("error");
    	}  	
    }
    /**
    * ATT .     
    * @return sim porting modify
    */
    public function sim_port_modify(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$phoneNumber  = "6467976577";
    	$url 		  = 'portin/'.$phoneNumber.'?MVNO='.$mvno_key;

    	$address    	= new \stdClass();
    	$port       	= new \stdClass();
    	$subscriber 	= new \stdClass();
    	$oldserprovider = new \stdClass();
    	$authorizer     = new \stdClass();


    	$serviceZipCode     = "75075";
    	$externalAccountId  = "287287504263";

    	$firstName = "Jane";
    	$lastName  = "Doe";

    	$address->streetNumber 			= '111';
    	$address->streetDirection 		= 'NW';
    	$address->streetName 			= '100';
    	$address->streetType 			= 'AVE';
    	$address->city 					= 'Plantation';
    	$address->state 				= 'FL';
    	$address->zipCode 				= '75075';

    	$oldserprovider->accountNumber = "1054429489";

    	$subscriber->address 		= $address;
    	$authorizer->address        = $address;
    	$authorizer->firstName 		= $firstName;
    	$authorizer->lastName 		= $lastName;
    	

    	$port->serviceZipCode 		= $serviceZipCode;
    	$port->externalAccountId    = $externalAccountId;

    	$port->subscriber 			= $subscriber;
    	$port->authorizer 			= $authorizer;
    	$port->oldServiceProvider 	= $oldserprovider;

    	$result = AttHelper::call_api_request($url,json_encode($port),'PATCH');
    	if($result->status == 200){
    		print_r($result);
    	}else{
    		print_r($result);
    		print_r("error");
    	}  	
    }
    /**
    * ATT .     
    * @return sim porting delete request
    */
    public function sim_port_delete(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$phoneNumber  = "6467976577";
    	$zipCode      = "33324";
    	$url 	= 'portin/'.$phoneNumber.'?MVNO='.$mvno_key.'&zipCode='.$zipCode;

    	$result = AttHelper::call_api_request($url,'','DELETE'); 

    	if($result->status == 200){
    		print_r($result);
    	}else{
    		print_r("error");
    	}  	
    }
    /**
    * ATT .     
    * @return sim account status
    */
    public function sim_account_status(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$phoneNumber  = "5555551234";
    	$url 	= 'accounts/'.$phoneNumber.'?MVNO='.$mvno_key;

    	$result = AttHelper::call_api_request($url); 

    	if($result->status == 200){
    		$res = $result->response;
    		if($res->resultCode == 0){
    			print_r($res);
    			print_r("success");
    		}else{
    			print_r($res);
    			print_r("error");
    		}
    	}else{
    		print_r("error");
    	}  	
    }
    /**
    * ATT .     
    * @return sim service info
    */
    public function sim_service_info(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$phoneNumber  = "5555551234";
    	$url 	= 'services/'.$phoneNumber.'?MVNO='.$mvno_key;

    	$result = AttHelper::call_api_request($url); 

    	if($result->status == 200){
    		$res = $result->response;
    		if($res->resultCode == 0){
    			print_r($res);
    			print_r("success");
    		}else{
    			print_r($res);
    			print_r("error");
    		}
    	}else{
    		print_r("error");
    	}  	
    }
    /**
    * ATT .     
    * @return sim service modify (Suspend, Restore and Cancel)
    */
    public function sim_service_modify_actions(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$phoneNumber  = "5555551234";
    	$statusType   = "SUSPEND";
    	$reason       = "EE";

    	$url 	= 'services/'.$phoneNumber.'?MVNO='.$mvno_key;
    	$obj    = new \stdClass();
    	$obj->reasonCode = $reason;

    	switch ($statusType) {
    		case 'SUSPEND':
    			$obj->mode = "Suspend";
    			break;
    		case 'RESUME':
    			$obj->mode = "Restore";
    			break;
    		case 'CANCEL':
    			$obj->mode = "Cancel";
    			break;
    		
    		default:
    			break;
    	}

    	$result = AttHelper::call_api_request($url,json_encode($obj),'PATCH'); 

    	if($result->status == 200){
    		$res = $result->response;
    		if($res->resultCode == 0){
    			print_r($res);
    			print_r("success");
    		}else{
    			print_r($res);
    			print_r("error");
    		}
    	}else{
    		print_r($result);
    		print_r("error");
    	}  	
    }
    /**
    * ATT .     
    * @return sim service modify (IMEI, SIM)
    */
    public function sim_service_modify_equipment(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$phoneNumber  = "5555551234";
    	$type   	  = "SIM";
    	$serviceZipCode    = "75075";
    	$newequip          = "89014104270225985364";

    	$url 		= 'services/'.$phoneNumber.'?MVNO='.$mvno_key;
    	$equipvals 	= [];
    	array_push($equipvals, ['name'=>'serviceZipCode','value'=>$serviceZipCode]);

    	$obj    	= new \stdClass();

    	switch ($type) {
    		case 'IMEI':
    			array_push($equipvals, ['name'=>'IMEI','value'=>$newequip]);
    			break;
    		case 'SIM':
    			array_push($equipvals, ['name'=>'SIM','value'=>$newequip]);
    			break;
    		default:
    			break;
    	}

    	$obj->characteristics = $equipvals;

    	$result = AttHelper::call_api_request($url,json_encode($obj),'PATCH'); 

    	if($result->status == 200){
    		$res = $result->response;
    		if($res->resultCode == 0){
    			print_r($res);
    			print_r("success");
    		}else{
    			print_r($res);
    			print_r("error");
    		}
    	}else{
    		print_r($result);
    		print_r("error");
    	}  	
    }
    /**
    * ATT .     
    * @return sim service modify (International (Calling,Roaming,Day Pass,Temporary Data Block) )
    */
    public function sim_service_modify_features(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$phoneNumber  = "5555551234";
    	$type   	  = "CALLING";
    	$effectiveDate = "2020-09-07";
    	$featureval    = "No"; //Yes or No

    	$url 		= 'services/'.$phoneNumber.'?MVNO='.$mvno_key;


    	$featurearray 	= [];
    	$obj    		= new \stdClass();

    	switch ($type) {
    		case 'CALLING':
    			array_push($featurearray, ['name'=>'intlCalling','value'=>$featureval]);
    			break;
    		case 'ROAMING':
    			array_push($featurearray, ['name'=>'intlRoaming','value'=>$featureval]);
    			break;
    		case 'DAYPASS':
    			array_push($featurearray, ['name'=>'FTRS Intl Day Pass','value'=>$featureval]);
    			break;
    		case 'DATABLOCK':
    			array_push($featurearray, ['name'=>'FTRS Data Blocking','value'=>$featureval]);
    			break;
    		default:
    			break;
    	}
    	$obj->effectiveDate   = $effectiveDate;
    	$obj->characteristics = $featurearray;

    	$result = AttHelper::call_api_request($url,json_encode($obj),'PATCH'); 

    	if($result->status == 200){
    		$res = $result->response;
    		if($res->resultCode == 0){
    			print_r($res);
    			print_r("success");
    		}else{
    			print_r($res);
    			print_r("error");
    		}
    	}else{
    		print_r($result);
    		print_r("error");
    	}  	
    }
    /**
    * ATT .     
    * @return sim service modify plan
    */
    public function sim_service_modify_plan(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$phoneNumber  = "5555551234";

    	$url 		  = 'services/'.$phoneNumber.'?MVNO='.$mvno_key;

    	$planName     = "Mobile select";
    	$serviceType  = "Data And Voice";
    	$size         = "10GB";
    	$tethering    = "No";

    	$planarray    = [['name'=>'planName','value'=>$planName],['name'=>'serviceType','value'=>$serviceType],['name'=>'size','value'=>$size],['name'=>'tethering','value'=>$tethering]];

    	$obj    		= new \stdClass();
    	$obj->characteristics = $planarray;

    	$result = AttHelper::call_api_request($url,json_encode($obj),'PATCH'); 

    	if($result->status == 200){
    		$res = $result->response;
    		if($res->resultCode == 0){
    			print_r($res);
    			print_r("success");
    		}else{
    			print_r($res);
    			print_r("error");
    		}
    	}else{
    		print_r($result);
    		print_r("error");
    	}  	
    }
    /**
    * ATT .     
    * @return sim usage of provided number
    */
    public function sim_usage(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$phoneNumber  = "5555551234";
    	$fromDate     = '2020-01-01';
    	$toDate       = '2020-09-07';
    	$url 		  = 'usage?msisdn='.$phoneNumber.'&fromDate='.$fromDate.'&toDate='.$toDate.'&MVNO='.$mvno_key;

    	$result = AttHelper::call_api_request($url); 

    	if($result->status == 200){
    		$res = $result->response;
    		if($res->resultCode == 0){
    			print_r($res);
    			print_r("success");
    		}else{
    			print_r($res);
    			print_r("error");
    		}
    	}else{
    		print_r($result);
    		print_r("error");
    	}  	
    }
    /**
    * ATT .     
    * @return all usage of active subscriptions
    */
    public function sim_usage_all(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$url 	= 'usage-all/?MVNO='.$mvno_key.'&filter=Month';

    	$result = AttHelper::call_api_request($url); 

    	if($result->status == 200){
    		$res = $result->response;
    		if($res->resultCode == 0){
    			print_r($res);
    			print_r("success");
    		}else{
    			print_r($res);
    			print_r("error");
    		}
    	}else{
    		print_r($result);
    		print_r("error");
    	}  	
    }
    /**
    * ATT .     
    * @return sim usage of provided MDN
    */
    public function sim_usage_mdn(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$phoneNumber  = "5555551234";
    	$filter       = 'month'; //filter - month / week / day
    	$url 		  = 'usage/'.$phoneNumber.'?MVNO='.$mvno_key.'&filter='.$filter;

    	$result = AttHelper::call_api_request($url); 

    	if($result->status == 200){
    		$res = $result->response;
    		if($res->resultCode == 0){
    			print_r($res);
    			print_r("success");
    		}else{
    			print_r($res);
    			print_r("error");
    		}
    	}else{
    		print_r($result);
    		print_r("error");
    	}  	
    }
    /**
    * ATT .     
    * @return sim usage sync date
    */
    public function sim_usage_syncdate(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$url 		  = 'usage/lastSyncDate?MVNO='.$mvno_key;

    	$result = AttHelper::call_api_request($url); 

    	if($result->status == 200){
    		$res = $result->response;
    		if($res->resultCode == 0){
    			print_r($res);
    			print_r("success");
    		}else{
    			print_r($res);
    			print_r("error");
    		}
    	}else{
    		print_r($result);
    		print_r("error");
    	}  	
    }
    /**
    * ATT .     
    * @return sim subscribers list
    */
    public function sim_subscribers_list(Request $request)
    {
    	$mvno_key     = Helper::get_option('att_mvno_key');
    	$status       = "All";
    	$pageSize     = 200;
    	$url 		  = 'accounts/subscribers/1?MVNO='.$mvno_key.'&status='.$status.'&pageSize='.$pageSize;

    	$result = AttHelper::call_api_request($url); 

    	if($result->status == 200){
    		$res = $result->response;
    		if($res->resultCode == 0){
    			print_r($res);
    			print_r("success");
    		}else{
    			print_r($res);
    			print_r("error");
    		}
    	}else{
    		print_r($result);
    		print_r("error");
    	}  	
    }
    /**
    * ATT .     
    * @return validate address
    */
    public function att_validate_address(Request $request)
    {
    	$address['USERID'] = '484GLOBA6477';
    	$address['address1'] = '';
    	$address['address2'] = '123 Main';
    	$address['city'] 	 = 'Neark';
    	$address['state'] 	 = 'NJ';
    	// $address['zip5'] 	 = '07104';

    	$xml      = AttHelper::create_address_xml($address);
    	$url      = '?API=Verify&XML='.$xml;
    	$result   = AttHelper::call_usps_api($url);

    	if($result->status == 200){
    		$res = $result->response;
    		print_r($res);
    	}else{
    		print_r($result);
    		print_r("error");
    	}  	
    }

}
