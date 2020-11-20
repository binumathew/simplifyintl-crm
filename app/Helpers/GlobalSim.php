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
}


?>
