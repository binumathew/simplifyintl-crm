<?php 
namespace App\Helpers;

use Helper;
use Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class AttHelper
{
  public static function call_api_request($url,$params = [],$method = 'GET')
  {
  	$api_url 		= Helper::get_option('att_api_endpoint');
  	$api_username 	= Helper::get_option('att_api_auth_username');
  	$api_userpwd 	= Helper::get_option('att_api_auth_pswd');


  	$client = new \GuzzleHttp\Client([
                'base_uri' => $api_url,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
  	$obj 		= new \stdClass();
  	$reqtype    = (is_array($params) || is_object($params)) ? 'form_params' : 'body'; 
  	$params     = (is_object($params)) ? json_decode(json_encode($params),TRUE) : $params;

  	try {
        $response = $client->request($method, /* POST, GET, PUT, DELETE, etc*/
	    				$url,
	    				['auth' => [$api_username, $api_userpwd],
	    				$reqtype=>$params 
    				]);

    	$statuscode 	= $response->getStatusCode();
    	$obj->status 	= (int) $statuscode;

    	if (200 === $statuscode) {
    		$obj->response = json_decode($response->getBody());
		}

    } catch (ClientException $e) {
    	$obj->status = 400;
        $res = $e->getResponse();
	    $obj->response = json_decode($res->getBody()->getContents());

    } catch (RequestException $e) {
    	$obj->status = 503;
        $res = $e->getResponse();
	    $obj->response = json_decode($res->getBody()->getContents());

    } catch (\Exception $e) {
    	$obj->status = 500;
        $res = $e->getResponse();
	    $obj->response = json_decode($res->getBody()->getContents());
    }
   	return $obj;
  }

  public static function create_address_xml($data)
  {
  	
    $xml = '<?xml version="1.0"?><AddressValidateRequest USERID="'.$data['USERID'].'"><Address ID="0"><Address1>'.$data['address1'].'</Address1><Address2>'.$data['address2'].'</Address2><City>'.$data['city'].'</City><State>'.$data['state'].'</State><Zip5></Zip5><Zip4></Zip4></Address></AddressValidateRequest>';

	return $xml;
  }

  public static function call_usps_api($url)
  {
  	$base_uri = Helper::get_option('usps_api_endpoint');
  	$client   = new \GuzzleHttp\Client([
                        'base_uri' => $base_uri,
                        'headers' => [
                            'Accept' => 'application/xml',
                            'Content-Type' => 'application/xml',
                        ],
                    ]);
  	$obj 		= new \stdClass();

  	try {
        $request = $client->get($url);

    	$statuscode 	= $request->getStatusCode();
    	$obj->status 	= (int) $statuscode;

    	if (200 === $statuscode) {
    		$res  = $request->getBody()->getContents();
    		$obj->response = simplexml_load_string($res);
		}

    } catch (ClientException $e) {
    	$obj->status = 400;
        $res = $e->getResponse();
	    $obj->response = json_decode($res->getBody()->getContents());

    } catch (RequestException $e) {
    	$obj->status = 503;
        $res = $e->getResponse();
	    $obj->response = json_decode($res->getBody()->getContents());

    } catch (ConnectException $e) {
    	$obj->status = 500;
        $res = $e->getResponse();
	    $obj->response = json_decode($res->getBody()->getContents());
    }
   	return $obj;
  }
}