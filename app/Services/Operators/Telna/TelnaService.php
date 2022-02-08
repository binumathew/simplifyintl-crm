<?php
namespace App\Services\Operators\Telna;

use App\Helpers\Supplier\Telna;
use Log;

class TelnaService {

	private static $client;

	public function __construct(){

		self::$client = new Telna();
    }

	public static function get_sim_info($iccid){
		try{
			$endpoint   = self::prepareEndpoint(['sim',$iccid,'info']);
			$response 	= self::$client->call("get",$endpoint);
			return ($response) ? $response : false;
		}catch(\Exception $e){
			Log::error('Telna-get-sim-info',[
				'iccid'=> $iccid,
                'error' => $e->getMessage()
            ]);
			return false;
		}
	}
	public static function set_sim_balance_drain($iccid,$params){
		try{
			$endpoint   = self::prepareEndpoint(['sim',$iccid,'balanceDrain']);
			$response 	= self::$client->call("post",$endpoint,$params);
			return ($response == null) ? true : false;
		}catch(\Exception $e){
			Log::error('Telna-set-sim-balance-drain',[
				'iccid'=> $iccid,
                'error' => $e->getMessage()
            ]);
			return false;
		}
	}
	public static function sim_activate($iccid,$params){
		try{
			$endpoint   = self::prepareEndpoint(['sim',$iccid,'addPackage']);
			$response 	= self::$client->call("post",$endpoint,$params);
			return ($response) ? $response : false;
		}catch(\Exception $e){
			Log::error('Telna-sim-activate',[
				'iccid'=> $iccid,
                'error' => $e->getMessage()
            ]);
			return false;
		}
	}
	public static function set_sim_activate($package_id,$params){
		try{
			$endpoint   = self::prepareEndpoint(['package',$package_id,'status']);
			$response 	= self::$client->call("post",$endpoint,$params);
			return ($response == null) ? true : false;
		}catch(\Exception $e){
			Log::error('Telna-set-sim-activate',[
				'iccid'=> $iccid,
                'error' => $e->getMessage()
            ]);
			return false;
		}
	}
	private static function prepareEndpoint($params){
		$url    = '';
		foreach ($params as $key => $value) {
			$url .= '/'.$value;
		}
		return $url;
	}
}