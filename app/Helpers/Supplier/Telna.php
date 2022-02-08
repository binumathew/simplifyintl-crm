<?php

namespace App\Helpers\Supplier;

use GuzzleHttp\Client;
use App\Handlers\TelnaGuzzleApiHandler;
use Log;

class Telna {

	private static $client;
	private static $headers;
	private static $route;

	public function __construct(){
		self::prepareClient();
    }

	public static function call($verb,$route,array $data = []){
        try{

        	self::prepareRoute($route);
            $apiHandler = new TelnaGuzzleApiHandler(self::$route,
                $data,
                self::$client
            );
            $response = $apiHandler->$verb();
            return $response;
        }catch(\Exception $e){
        	Log::error('Telna-api-call',[
        		'method'=> $verb,
				'route'=> $route,
				'data'=> $data,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    private static function getApiHeaders(){
        self::$headers = [
        	"ApiKey" => config('telna.api.headers.api_key'),
            "Authorization" =>base64_encode(config('telna.api.headers.login_id').':'.config('telna.api.headers.access_token')),
            "Content-Type"=> config('telna.api.headers.content_type')
        ];
    }

    private static function prepareClient(){

    	self::getApiHeaders();

        self::$client = new Client([
            'base_uri' => config("telna.api.base_url"),
            'headers' => self::$headers
        ]);
    }

    private static function prepareRoute($route){

    	$route 	 	= ($route[0] != "/") ? "/".$route : $route;

    	$base_path 	= (substr(config("telna.api.base_url") , -1)=='/') ? config("telna.api.base_path") :  '/'.config("telna.api.base_path");

    	$endpoint 	= (substr($base_path , -1)=='/') ? $base_path.config("telna.api.distributor_id") :  $base_path.'/'.config("telna.api.distributor_id");

    	self::$route =  $endpoint.$route;
  
    }

}