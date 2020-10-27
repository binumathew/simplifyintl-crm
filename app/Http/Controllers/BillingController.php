<?php



namespace App\Http\Controllers;

use Illuminate\Http\Request;

use SoapClient;

use Carbon;

use DwpHelper;

use Session;
use Illuminate\Support\Facades\Validator;

class BillingController extends Controller

{

    public function __construct()

    {

        $this->middleware('auth');

    }
    /**
    * Soap WSDL Login
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function api_login(Request $request){
    	$SOAPClient      = DwpHelper::initiate_soap_client();

    	if(!Session::has('affinityToken')){
    		$token = DwpHelper::generate_identity_token();
    		Session::put('affinityToken',$token);
    		Session::save();
    	}

    	$token 		= Session::get('affinityToken');
    	// Session::forget('affinityToken');
    	// Session::save();
    }
    public function create_new_site(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();
    	if($client->token){

	    	$SOAPClient     = $client->SOAPClient;
	    	$token 			= $client->token;

	    	$createNewSite = $SOAPClient->CreateNewSiteByID(array("IdentityToken" => $token, "siteName" => "Avoo-TestSite", "siteRef" => "AVOO123", "dealerID" => 1,'companyID'=>-1));
			if($createNewSite){
				if($createNewSite->CreateNewSiteByIDResult->ResponseCode == 0){
					$siteId = $createNewSite->CreateNewSiteByIDResult->siteID;
				}
			}
		}
    }
    public function get_site_details(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();
    	if($client){
	    	if($client->token){
		    	$SOAPClient     = $client->SOAPClient;
		    	$token 			= $client->token;

		    	$siteId  = (int)'10005';
				$getSite = $SOAPClient->GetSiteByID(['IdentityToken'=>$token,'siteID'=>10005]);
				if($getSite){
					$obj = new \stdClass();
					$obj->status = 400;
					if($getSite->GetSiteByIDResult->ResponseCode == 0){
						$siteDetails = $getSite->siteDetails;
						$responseArray = DwpHelper::load_from_xml($siteDetails->any);
						$obj->status  = 200;
						$obj->response = $responseArray; 
						print_r($obj);
					}else{
						print_r($obj);
					}
				}
			}
		}
    }
    public function add_cli(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();

    	if($client->token){
	    	$SOAPClient     = $client->SOAPClient;
	    	$token 			= $client->token;

	    	$startDate = Carbon::now()->format('d/m/Y');
			$endDate   = Carbon::now()->addmonth(1)->format('d/m/Y');

			// $addCli = $SOAPClient->CreateNewCLI(['IdentityToken'=>$token,'SiteID'=>$siteId,'CLINumber'=>'07766742689','TerminatingNumber'=>'','CLIType'=>"MOBILE",'StartDate'=>$startDate,'EndDate'=>$endDate]);

			// if($addCli){
			// 	if($addCli->CreateNewCLIResult->ResponseCode == 0){
			// 		$newCli = $addCli->CreateNewCLIResult->Result;
			// 	}
			// }
			$newCli = '5';
			print_r($newCli);
		}
    }
    public function get_site_byref(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();

    	if($client->token){
	    	$SOAPClient     = $client->SOAPClient;
	    	$token 			= $client->token;

	    	$siteRef    = 'AVOO123'; 
	    	$getSite = $SOAPClient->GetSiteIDByRef(['IdentityToken'=>$token,'siteRef'=>$siteRef]);
	    	if($getSite){
				if($getSite->GetSiteIDByRefResult->ResponseCode == 0){
					$siteID = $getSite->siteID;
				}
			}
		}
    	print_r($siteID);
    }
    public function update_site_byId(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();

    	if($client->token){
	    	$SOAPClient     = $client->SOAPClient;
	    	$token 			= $client->token;

	    	$siteId     = (int)'10005';
	    	$req 		= ['SiteName'=>'Avoo-TestSite','SiteRef'=>'avoo12345678','CompanyName'=>'Avoo'];
    		$details 	= [];
	    	foreach ($req as $rkey => $rlist) {
	    		$details[] = ['Key'=>$rkey,'Value'=>$rlist];
	    	}
			$updateSite = $SOAPClient->UpdateSiteByID(['IdentityToken'=>$token,'siteID'=>$siteId,'siteDetails'=>$details]);
			if($updateSite){
				if($updateSite->UpdateSiteByIDResult->ResponseCode == 0){
					print_r("updated");
				}
			}
	    }
    }
    public function get_site_address(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();

    	if($client->token){
	    	$SOAPClient     = $client->SOAPClient;
	    	$token 			= $client->token;

	    	$siteId     = (int)'10005';
	    	$addressType = 'Billing';
	    	$getAddress = $SOAPClient->GetSiteAddressByID(['IdentityToken'=>$token,'siteID'=>$siteId,'addressType'=>$addressType]);
	    	if($getAddress){
				if($getAddress->GetSiteAddressByIDResult->ResponseCode == 0){
					print_r($getAddress);
				}
			}
		}
    }
    public function update_site_address(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();

    	if($client->token){
	    	$SOAPClient     = $client->SOAPClient;
	    	$token 			= $client->token;

	    	$siteId     = (int)'10005';

	    	$addressType = 'Billing';
	    	$address1   = "7 Sherwood House";
	 		$address2 	= "Walderslade Centre";
	 		$address3 	= "";
	 		$town  		= "Chathamghu"; // Town
	 		$country    = "United Kingdom"; // Country
	 		$postcode   = "ME5 9UD"; // Post Code

	    	$updateAddress = $SOAPClient->UpdateSiteAddressByID(['IdentityToken'=>$token,'siteID'=>$siteId,'addressType'=>$addressType,'address1'=>$address1,'address2'=>$address2,'address3'=>$address3,'address4'=>$town,'address5'=>$country,'address6'=>$postcode]);
	    	if($updateAddress){
				if($updateAddress->UpdateSiteAddressByIDResult->ResponseCode == 0){
					print_r("updated");
				}
			}
	    	print_r($updateAddress);
	    }
    }
    public function update_site_address_type(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();

    	if($client->token){
	    	$SOAPClient     = $client->SOAPClient;
	    	$token 			= $client->token;

	    	$siteId     = (int)'10005';
	    	$updateAddressType = $SOAPClient->UpdateSiteAddressTypeByID(['IdentityToken'=>$token,'siteID'=>$siteId,'currentAddressType'=>'Billing','newAddressType'=>'Site']);
	    	if($updateAddressType){
				if($updateAddressType->UpdateSiteAddressTypeByIDResult->ResponseCode == 0){
					print_r("updated");
				}
			}
	    	print_r($updateAddressType);
	    }
    }
    public function update_site_contact(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();

    	if($client->token){
	    	$SOAPClient     = $client->SOAPClient;
	    	$token 			= $client->token;

	    	$siteId     = (int)'10005';

	    	$contactType    = 'Billing';
	    	$contactTitle   = "Mr";
	 		$contactFirstName 	= "Arun";
	 		$contactLastName 	= "Raj cherukunnil";
	 		$contactTelephoneNumber  = ""; 
	 		$contactMobileNumber    = "07714345454"; 
	 		$contactEmailAddress   = "test@gmail.com";

	    	$updatecontact = $SOAPClient->UpdateSiteContactByID(['IdentityToken'=>$token,'siteID'=>$siteId,'contactType'=>$contactType,'contactTitle'=>$contactTitle,'contactFirstName'=>$contactFirstName,'contactLastName'=>$contactLastName,'contactTelephoneNumber'=>$contactTelephoneNumber,'contactMobileNumber'=>$contactMobileNumber,'contactEmailAddress'=>$contactEmailAddress]);
	    	if($updatecontact){
				if($updatecontact->UpdateSiteContactByIDResult->ResponseCode == 0){
					print_r("updated");
				}
			}
	    	print_r($updatecontact);
	    }
    }
    public function get_site_contact(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();

    	if($client->token){
	    	$SOAPClient     = $client->SOAPClient;
	    	$token 			= $client->token;

	    	$siteId     = (int)'10005';
	    	$contactType    = 'Billing';
	    	$getcontact = $SOAPClient->GetSiteContactByID(['IdentityToken'=>$token,'siteID'=>$siteId,'contactType'=>$contactType]);
	    	if($getcontact){
				if($getcontact->GetSiteContactByIDResult->ResponseCode == 0){
					print_r($getcontact);
				}
			}
		}
    }
    public function create_site_email(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();

    	if($client->token){
	    	$SOAPClient     = $client->SOAPClient;
	    	$token 			= $client->token;

	    	$siteId     = (int)'10005';
	    	$emailAddress    = 'testbilling22@gmail.com';
	    	$emailBill       = false;

	    	$addemail = $SOAPClient->CreateNewSiteEmailAddressByID(['IdentityToken'=>$token,'siteID'=>$siteId,'emailAddress'=>$emailAddress,'emailBill'=>$emailBill]);
	    	if($addemail){
				if($addemail->CreateNewSiteEmailAddressByIDResult->ResponseCode == 0){
					print_r("added");
				}else{
					$res = $addemail->CreateNewSiteEmailAddressByIDResult->ResponseMessage;
					print_r($res);
				}
			}
		}
    }
    public function get_site_email(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();

    	if($client->token){
	    	$SOAPClient     = $client->SOAPClient;
	    	$token 			= $client->token;

	    	$siteId     = (int)'10005';

	    	$getemail = $SOAPClient->GetSiteEmailAddressesByID(['IdentityToken'=>$token,'siteID'=>$siteId]);
	    	if($getemail){
				if($getemail->GetSiteEmailAddressesByIDResult->ResponseCode == 0){
					$emailDetails  = $getemail->emailAddresses;
					$responseArray = DwpHelper::load_from_xml($emailDetails->any);
					print_r($responseArray);
				}
			}
		}
    }
    public function update_site_email(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();

    	if($client->token){
	    	$SOAPClient     = $client->SOAPClient;
	    	$token 			= $client->token;

	    	$siteId     = (int)'10005';
	    	$emailAddress    = 'testbilling2ffds@gmail.com';
	    	$emailBill       = true;
	    	$updateemail = $SOAPClient->UpdateSiteEmailAddressByID(['IdentityToken'=>$token,'siteID'=>$siteId,'emailAddress'=>$emailAddress,'emailBill'=>$emailBill]);
	    	if($updateemail){
				if($updateemail->UpdateSiteEmailAddressByIDResult->ResponseCode == 0){
					print_r("updated");
				}
			}
	    	print_r($updateemail);
	    }
    }
    public function delete_site_email(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();

    	if($client->token){
	    	$SOAPClient     = $client->SOAPClient;
	    	$token 			= $client->token;

	    	$siteId     = (int)'10005';
	    	$emailAddress    = 'testbilling@gmail.com';

	    	$deleteemail = $SOAPClient->DeleteSiteEmailAddressByID(['IdentityToken'=>$token,'siteID'=>$siteId,'emailAddress'=>$emailAddress,]);
	    	if($deleteemail){
				if($deleteemail->DeleteSiteEmailAddressByIDResult->ResponseCode == 0){
					print_r("Deleted");
				}
			}
	    	print_r($deleteemail);
	    }
    }
    public function update_cli(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();

    	if($client->token){
	    	$SOAPClient     = $client->SOAPClient;
	    	$token 			= $client->token;

	    	$cliID      = (int)5;
	    	$description = 'Test cli description';
	    	$updatecli = $SOAPClient->UpdateCLIByID(['IdentityToken'=>$token,'cliID'=>$cliID,'description'=>$description]);
	    	if($updatecli){
				if($updatecli->UpdateCLIByIDResult->ResponseCode == 0){
					print_r("updated cli");
				}
			}
		}
    }
    public function update_cli_details(Request $request){
    	$client     	= DwpHelper::initiate_soap_client();

    	if($client->token){
	    	$SOAPClient     = $client->SOAPClient;
	    	$token 			= $client->token;

	    	$cliID      = (int)5;
	  		$req = ['description'=>'update cli description on 04-09-2020'];
	    	$details = [];
	    	foreach ($req as $rkey => $rlist) {
	    		$details[] = ['Key'=>$rkey,'Value'=>$rlist];
	    	}

	    	$updatecli = $SOAPClient->UpdateCLIDetailsByID(['IdentityToken'=>$token,'cliID'=>$cliID,'cliDetails'=>$details]);
	    	if($updatecli){
				if($updatecli->UpdateCLIDetailsByIDResult->ResponseCode == 0){
					print_r("updated cli");
				}
			}
		}
    }
}