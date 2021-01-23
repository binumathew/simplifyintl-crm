<?php
namespace App\Helpers;

use Spatie\ArrayToXml\ArrayToXml;
use Carbon;
use GuzzleHttp\Client;
use Utils;
use Helper;
use Log;

class GlobalSim {

    public static function get($params,$request){

        try{
            $client = new Client();
            $options =[
                'headers' => [
                    'Content-Type' => 'text/xml; charset=UTF8',
                ],
                'body' => ArrayToXml::convert($params,['rootElementName' => $request],true, 'UTF-8')
            ];

            $result = $client->get(config('services.globalsim.api'),$options);
            return $result->getBody()->getContents();
        }
        catch(ClientException $e){
            return false;
        } catch(RequestException $e){
            return false;
        }catch(\Exception $e){
            return false;
        }
    }
    public static function getCallHistory($msisdn,$from,$to){
            try{
                $params = [
                    'MSISDN' => $msisdn,
                    'StartDate' => $from,
                    'EndDate' => $to,
                    'Authentication' => [
                        'Username' => config('services.globalsim.username'),
                        'Password' => config('services.globalsim.password'),
                    ]
                    ];
                $result = self::get($params,'GetCallsForAnMSISDN');
                if($result !== false) {
                    return  json_decode(json_encode(simplexml_load_string($result)),true);
                }
                return false;
            }catch(\Exception $e){
                return false;
            }
    }
    public static function getCalls($msisdn,$from,$to){
        try{
            $params = [
                'MSISDN' => $msisdn,
                'StartDate' => $from,
                'EndDate' => $to,
                'Authentication' => [
                    'Username' => config('services.globalsim.username'),
                    'Password' => config('services.globalsim.password'),
                ]
                ];
            $result = self::get($params,'GetCallsForAnMSISDN');
            if($result !== false) {
                return  json_decode(json_encode(simplexml_load_string($result)),true);
            }
            return false;
        }catch(\Exception $e){
            return false;
        }
    }
    public static function getDataHistory($msisdn,$from,$to){
        try{
            $params = [
                'MSISDN' => $msisdn,
                'StartDate' => $from,
                'EndDate' => $to,
                'Authentication' => [
                    'Username' => config('services.globalsim.username'),
                    'Password' => config('services.globalsim.password'),
                ]
                ];
            $result = self::get($params,'DataGetFullCallHistoryForAnMSISDN');
            if($result !== false) {
                return  json_decode(json_encode(simplexml_load_string($result)),true);
            }
            return false;
        }catch(\Exception $e){
            return false;
        }
    }
    public static function getResellerAlias($reseller_id){
      try{
          $params = [
              'resellerid' => $reseller_id,
              'Authentication' => [
                  'Username' => config('services.globalsim.username'),
                  'Password' => config('services.globalsim.password'),
              ]
              ];
          $result = self::get($params,'GetAliasListByReseller');
          if($result !== false) {
              return  json_decode(json_encode(simplexml_load_string($result)),true);
          }
          return false;
      }catch(\Exception $e){
          return false;
      }
  }
    public static function AssignMsisdn($iccid){
        try{
            $params = [
                'sim' => [
                'iccid' =>$iccid,
                ],
                'Authentication' => [
                    'Username' => config('services.globalsim.username'),
                    'Password' => config('services.globalsim.password'),
                ]
                ];
            $result = self::get($params,'AssignMsisdn');
            if($result !== false) {
                return  json_decode(json_encode(simplexml_load_string($result)),true);
            }
            return false;
        }catch(\Exception $e){
            Log::error('AssignMsisdn',[
                'error' =>   $e->getMessage()
            ]);
            return false;
        }
    }
    public static function BundleSubscribe($subscribe){
        try{
            $params = [
                'bundleid' => $subscribe->bundle_id,
                'sim' => $subscribe->msisdn,
                'subscriptiondate' => $subscribe->date,
                'activateonfirstuse' => $subscribe->actfirstuse,
                'sendsms' => $subscribe->sendsms,
                'takepayment' =>$subscribe->takepayment,
                'repeatnumber'=>0,
                'Authentication' => [
                    'Username' => config('services.globalsim.username'),
                    'Password' => config('services.globalsim.password'),
                ]
                ];
            $result = self::get($params,'BundleSubscribe');
            if($result !== false) {
                return  json_decode(json_encode(simplexml_load_string($result)),true);
            }
            return false;
        }catch(\Exception $e){
            Log::error('BundleSubscribe',[
                'error' =>   $e->getMessage()
            ]);
            return false;
        }
    }
    public static function BundleUnSubscribe($unsubscribe){
        try{
            $params = [
                'bundleid' => $unsubscribe->bundle_id,
                'sim' => $unsubscribe->msisdn,
                'action' => $unsubscribe->action,
                'Authentication' => [
                    'Username' => config('services.globalsim.username'),
                    'Password' => config('services.globalsim.password'),
                ]
                ];
            $result = self::get($params,'BundleUnSubscribe');
            if($result !== false) {
                return  json_decode(json_encode(simplexml_load_string($result)),true);
            }
            return false;
        }catch(\Exception $e){
            Log::error('BundleUnSubscribe',[
                'error' =>   $e->getMessage()
            ]);
            return false;
        }
    }
    public static function AddCustomer($user,$setlimit){
        try{
            $params = [
                'Customer' =>[
                    'CompanyName'=>$user->username,
                    'Country'=>$user->country->short_code,
                    'BillingTrigger'=>$setlimit->bill_limit,
                    'WarningTrigger'=>$setlimit->warn_limit,
                    'LockTrigger'=>$setlimit->lock_limit,
                ],
                'Address' =>[
                    'Line1'=>$user->userDetail->address,
                    'Line2'=>$user->userDetail->city,
                    'Country'=>$user->country->short_code,
                    'Postcode'=>$user->userDetail->postal_code,
                ],
                'Authentication' => [
                    'Username' => config('services.globalsim.username'),
                    'Password' => config('services.globalsim.password'),
                ]
                ];
            $result = self::get($params,'AddCustomer');
            if($result !== false) {
                return  json_decode(json_encode(simplexml_load_string($result)),true);
            }
            return false;
        }catch(\Exception $e){
            Log::error('AddCustomer',[
                'error' =>   $e->getMessage()
            ]);
            return false;
        }
    }
    public static function ModifyCustomer($user,$setlimit,$esim_customer){
        try{
            $params = [
                'Customer' =>[
                    'ID'=>$esim_customer,
                    'CompanyName'=>$user->username,
                    'Country'=>$user->country->short_code,
                    'BillingTrigger'=>$setlimit->bill_limit,
                    'WarningTrigger'=>$setlimit->warn_limit,
                    'LockTrigger'=>$setlimit->lock_limit,
                ],
                'Address' =>[
                    'Line1'=>$user->userDetail->address,
                    'Line2'=>$user->userDetail->city,
                    'Country'=>$user->country->short_code,
                    'Postcode'=>$user->userDetail->postal_code,
                ],
                'Authentication' => [
                    'Username' => config('services.globalsim.username'),
                    'Password' => config('services.globalsim.password'),
                ]
                ];
            Log::info('modifycustomerrequest',[
                'request'=>$params
            ]);
            $result = self::get($params,'ModifyCustomer');
            if($result !== false) {
                return  json_decode(json_encode(simplexml_load_string($result)),true);
            }
            return false;
        }catch(\Exception $e){
            Log::error('ModifyCustomer',[
                'error' =>   $e->getMessage()
            ]);
            return false;
        }
    }
    public static function AddUser($user,$customer_id){
        try{
            $params = [
                'User' =>[
                    'Administrator'=>true,
                    'TimeZone'=>$user->country->time_zone,
                    'CustomerID'=>$customer_id,
                    'Username'=>$customer_id,
                    'Password'=>Helper::random(8),
                    'Title'=>'',
                    'FirstName'=>$user->first_name,
                    'MiddleInitials'=>'',
                    'Surname'=>$user->last_name,
                    'Country'=>$user->country->short_code,
                ],
                'Contact' =>[
                    'Email'=>$user->email,
                    'CallKeyID'=>$customer_id,
                ],
                'Address' =>[
                    'Line1'=>$user->userDetail->address,
                    'Line2'=>$user->userDetail->city,
                    'Country'=>$user->country->short_code,
                    'Postcode'=>$user->userDetail->postal_code,
                ],
                'Authentication' => [
                    'Username' => config('services.globalsim.username'),
                    'Password' => config('services.globalsim.password'),
                ]
                ];
            $result = self::get($params,'CreateUser');
            Log::error('AddUser',[
                'response' =>   $result
            ]);
            if($result !== false) {
                return  json_decode(json_encode(simplexml_load_string($result)),true);
            }
            return false;
        }catch(\Exception $e){
            Log::error('AddUser',[
                'error' =>   $e->getMessage()
            ]);
            return false;
        }
    }
    public static function ModifyUser($user,$customer_id,$esim_user){
        try{
            $params = [
                'User' =>[
                    'ID'=>$esim_user,
                    'Administrator'=>true,
                    'TimeZone'=>$user->country->time_zone,
                    'CustomerID'=>$customer_id,
                    'Username'=>$customer_id,
                    'Password'=>Helper::random(8),
                    'Title'=>'',
                    'FirstName'=>$user->first_name,
                    'MiddleInitials'=>'',
                    'Surname'=>$user->last_name,
                    'Country'=>$user->country->short_code,
                ],
                'Contact' =>[
                    'Email'=>$user->email,
                    'CallKeyID'=>$customer_id,
                ],
                'Address' =>[
                    'Line1'=>$user->userDetail->address,
                    'Line2'=>$user->userDetail->city,
                    'Country'=>$user->country->short_code,
                    'Postcode'=>$user->userDetail->postal_code,
                ],
                'Authentication' => [
                    'Username' => config('services.globalsim.username'),
                    'Password' => config('services.globalsim.password'),
                ]
                ];
            $result = self::get($params,'ModifyUser');
            if($result !== false) {
                return  json_decode(json_encode(simplexml_load_string($result)),true);
            }
            return false;
        }catch(\Exception $e){
            Log::error('ModifyUser',[
                'error' =>   $e->getMessage()
            ]);
            return false;
        }
    }
    public static function GetGlobalDetails($iccid){
        try{
            $params = [
                'iccid' =>$iccid,
                'Authentication' => [
                    'Username' => config('services.globalsim.username'),
                    'Password' => config('services.globalsim.password'),
                ]
                ];
            $result = self::get($params,'GetGlobalDetails');
            if($result !== false) {
                return  json_decode(json_encode(simplexml_load_string($result)),true);
            }
            return false;
        }catch(\Exception $e){
            Log::error('GetGlobalDetails',[
                'error' =>   $e->getMessage()
            ]);
            return false;
        }
    }

    public static function webhookContent($content){

        try{
            return json_decode(json_encode(simplexml_load_string($content)),true);
        }catch(\Exception $e){
            return false;
        }
    }

    public static function getSimInfo($iccid){
        try{
            $params = [
                'ICCID' =>$iccid,
                'Authentication' => [
                    'Username' => config('services.globalsim.username'),
                    'Password' => config('services.globalsim.password'),
                ]
                ];
            $result = self::get($params,'SimInformationByMSISDN');
            if($result !== false) {
                return  json_decode(json_encode(simplexml_load_string($result)),true);
            }
            return false;
        }catch(\Exception $e){
            Log::error('SimInformationByMSISDN',[
                'error' =>   $e->getMessage()
            ]);
            return false;
        }
    }
    public static function AddPrePaidCredit($esim_customer,$amount){
        try{
            $params = [
                'Credit' =>[
                    'CustomerID'=>$esim_customer,
                    'Amount'=>$amount,
                    'Narrative'=>"Add Credit ".$esim_customer,
                ],
                'Authentication' => [
                    'Username' => config('services.globalsim.username'),
                    'Password' => config('services.globalsim.password'),
                ]
                ];
            $result = self::get($params,'AddPrePaidCredit');
            if($result !== false) {
                return  json_decode(json_encode(simplexml_load_string($result)),true);
            }
            return false;
        }catch(\Exception $e){
            Log::error('AddPrePaidCredit',[
                'error' =>   $e->getMessage()
            ]);
            return false;
        }
    }

}


?>
