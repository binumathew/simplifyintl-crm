<?php
namespace App\Helpers;

use Spatie\ArrayToXml\ArrayToXml;
use Carbon;
use GuzzleHttp\Client;


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
        }catch(\Exception $e){
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
                'repeatnumber' => '',
                'Authentication' => [
                    'Username' => config('services.globalsim.username'),
                    'Password' => config('services.globalsim.password'),
                ]
                ];
            $result = self::get($params,'BundleSubscribe');
            if($result !== false) {
                return  json_decode(json_encode(simplexml_load_string($result)),true);
            }
        }catch(\Exception $e){
            return false;
        }
    }
    public static function AddCustomer($user,$setlimit){
        try{
            $params = [
                'Customer' =>[
                    'CompanyName'=>$user->first_name.' '.$user->last_name,
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
        }catch(\Exception $e){
            return false;
        }
    }
    
}


?>
