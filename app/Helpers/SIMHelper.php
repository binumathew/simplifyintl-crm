<?php 
namespace App\Helpers;

use Helper;
use Carbon;


class SIMHelper
{
  public static function call_sim_process_api($endpoint, $data, $method = 'post')
  {
    $api_url = Helper::get_option('bundle_purchase_endpoint');
    //$api_url = Helper::get_option('bundle_purchase_sandbox');
    $api_userpwd = Helper::get_option('bundle_purchase_auth_pswd');
    $api_url = $api_url.$endpoint;  

      $ch = curl_init($api_url);   
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));   
      if($method == 'post'){        
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }else if($method == 'patch'){
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH'); 
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_USERPWD, $api_userpwd);
      $output = curl_exec($ch);       
      
      if($errno = curl_errno($ch)) {
        $error_message = curl_strerror($errno);
        echo "cURL error ({$errno}):\n {$error_message}";
    }
    curl_close($ch);
      return json_decode($output); 
  }

  public static function dwp_process_api($xml_data)
  {
    $end_point = 'https://api.daisywholesale.com';//Helper::get_option('dwp_api_endpoint');
    //Helper::get_option('dwp_api_endpoint');//'https://api.daisywholesale.com';    
  
    $ch = curl_init($end_point);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml_data");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);       
    curl_close($ch);
    PRINT_R($output);

    echo chr(10).'====================================='.chr(10);
    // DIE();
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($output);
    print_r($xml);
    echo chr(10).'====================================='.chr(10);
    if(!$xml){
      echo '1111';
      die();
      return array('fault'=>1);
    }
    $json = json_encode($xml);
    $array = json_decode($json, true);
    $temp = array();
    foreach ($array as $k => $v) {
        foreach ($v as $k1 => $v1) {
            $temp[$k][$k1] = $v1;
        }
    }
    echo chr(10).'====================================='.chr(10);
    print_R($temp);
    $response = json_decode(json_encode($xml), true);
    echo chr(10).'====================================='.chr(10);
    print_r($response);
    //$response = json_decode($xml_data);    

    print_r($response);
    die();
    return $response;
  }

  public static function dwp_sim_account_disconnection_xml()
  {
    $api_user = Helper::get_option('dwp_auth_username');
    $api_pwd = Helper::get_option('dwp_auth_password');

    $xml_data = '<?xml version="1.0"?>
    <Request module="dwapi" call="mobile_account_disconnections" id="'.Helper::unique_code(32).'" version="1.0">
      <block name="auth">
        <a name="username" format="text">'. $api_user .'</a>
        <a name="password" format="password">'. $api_pwd .'</a>
      </block>
    </Request>';
    return $xml_data;
  }

  public static function dwp_sim_activate_xml()
  {
    $api_user = Helper::get_option('dwp_auth_username');
    $api_pwd = Helper::get_option('dwp_auth_password');
    
    $xml_data = '<?xml version="1.0"?>
    <Request module="dwapi" call="mobile_activate_sim" id="bd04df66fb25e8f78889680e9368a83e" version="1.0">
      <block name="auth">
        <a name="username" format="text">'. $api_user .'</a>
        <a name="password" format="password">'. $api_pwd .'</a>
      </block>
      <a name="mobile" format="phone">078355238523</a>
      <a name="network" format="text">Vodafone</a>
      <a name="sim-serial" format="text">89441000300307293021</a>
      <a name="sim-type" format="text">standard</a>
    </Request>';
    return $xml_data;
  }

  public static function dwp_sim_add_bolt_on_xml()
  {
    $api_user = Helper::get_option('dwp_auth_username');
    $api_pwd = Helper::get_option('dwp_auth_password');

    $xml_data = '<?xml version="1.0"?>
    <Request module="dwapi" call="mobile_add_bolt_on" id="28ffbb597996f7d26f171f5d8d9f7aa4" version="1.0">
      <block name="auth">
        <a name="username" format="text">'. $api_user .'</a>
        <a name="password" format="password">'. $api_pwd .'</a>
      </block>
      <a name="change-id" format="counting">86096</a>
      <a name="mobile-number" format="phone">072473811661</a>
      <a name="product-id" format="counting">74518</a>
      <a name="service-id" format="counting">25736</a>
    </Request>';
    return $xml_data;
  }

  public static function dwp_sim_available_bolt_ons_xml()
  {
    $api_user = Helper::get_option('dwp_auth_username');
    $api_pwd = Helper::get_option('dwp_auth_password');
    
    $xml_data = '<?xml version="1.0"?>
    <Request module="dwapi" call="mobile_available_bolt_ons" id="'.Helper::unique_code(32).'" version="1.0">
      <block name="auth">
        <a name="username" format="text">'. $api_user .'</a>
        <a name="password" format="password">'. $api_pwd .'</a>
      </block> 
      <a name="mobile-number" format="phone">07525217081</a>     
    </Request>';
    // <a name="limit" format="counting">1000</a>
    //   <a name="mobile-number" format="phone">072444135626</a>
    //   <a name="page" format="counting">1</a>
    //   <a name="service-id" format="counting">2801</a>
    return $xml_data;
  }

  public static function dwp_sim_check_pac_xml()
  {
    $api_user = Helper::get_option('dwp_auth_username');
    $api_pwd = Helper::get_option('dwp_auth_password');
    
    $xml_data = '<?xml version="1.0"?>
    <Request module="dwapi" call="mobile_check_pac" id="1003b7ac92bf953bdae2e3c57b46dbcd" version="1.0">
      <block name="auth">
        <a name="username" format="text">'. $api_user .'</a>
        <a name="password" format="password">'. $api_pwd .'</a>
      </block>
      <a name="mobile-number" format="phone">076687552534</a>
      <a name="pac" format="text">VUK941670</a>
    </Request>';
    return $xml_data;
  }

  public static function dwp_sim_check_xml()
  {
    $api_user = Helper::get_option('dwp_auth_username');
    $api_pwd = Helper::get_option('dwp_auth_password');
    
    $xml_data = '<?xml version="1.0"?>
    <Request module="dwapi" call="mobile_check_sim" id="0aa30c7257aaadc7659b396b5e7bf961" version="1.0">
      <block name="auth">
        <a name="username" format="text">'. $api_user .'</a>
        <a name="password" format="password">'. $api_pwd .'</a>
      </block>
      <a name="network" format="text">Vodafone</a>
      <a name="sim-serial" format="text">89441000300307293021</a>
    </Request>';
    return $xml_data;
  }

  public static function dwp_sim_lookup_cli_xml()
  {
    $api_user = Helper::get_option('dwp_auth_username');
    $api_pwd = Helper::get_option('dwp_auth_password');
    
    $xml_data = '<?xml version="1.0"?>
    <Request module="dwapi" call="mobile_lookup_sim_cli" id="70a62087241d89b6fc683e1eb4d77989" version="1.0">
      <block name="auth">
        <a name="username" format="text">'. $api_user .'</a>
        <a name="password" format="password">'. $api_pwd .'</a>
      </block>
      <a name="network" format="text">Vodafone</a>
      <a name="sim-serial" format="text">89441000300307293021</a>
    </Request>';
    return $xml_data;
  }

  public static function dwp_sim_order_new_xml()
  {
    $api_user = Helper::get_option('dwp_auth_username');
    $api_pwd = Helper::get_option('dwp_auth_password');
    
    $xml_data = '<?xml version="1.0"?>
    <Request module="dwapi" call="mobile_order_new" id="1e740d51cdc294612cb27ef499dd1ec2" version="1.0">
      <block name="auth">
        <a name="username" format="text">'. $api_user .'</a>
        <a name="password" format="password">'. $api_pwd .'</a>
      </block>
      <a name="assignto-username" format="text">Joe.Doe</a>
      <a name="customer-purchase-order-reference" format="text">lorem ipsum</a>
      <a name="name" format="text">New order for Joe.Doe</a>
    </Request>';
    return $xml_data;
  }

  public static function dwp_sim_service_products_xml()
  {
    $api_user = Helper::get_option('dwp_auth_username');
    $api_pwd = Helper::get_option('dwp_auth_password');
    
    $xml_data = '<?xml version="1.0"?>
    <Request module="dwapi" call="mobile_available_service_products" id="37a4038b1dd3d14612f068155ad4f2e4" version="1.0">
      <block name="auth">
        <a name="username" format="text">'. $api_user .'</a>
        <a name="password" format="password">'. $api_pwd .'</a>
      </block>

      <a name="mobile-number" format="phone">07525217081</a>

    </Request>';
    return $xml_data;
  }

  public static function dwp_sim_check_xml2()
  {
    $api_user = Helper::get_option('dwp_auth_username');
    $api_pwd = Helper::get_option('dwp_auth_password');
    
    $xml_data = '';
    return $xml_data;
  }

  public static function dwp_sim_check_xml3()
  {
    $api_user = Helper::get_option('dwp_auth_username');
    $api_pwd = Helper::get_option('dwp_auth_password');
    
    $xml_data = '';
    return $xml_data;
  }

  public static function dwp_sim_check_xml4()
  {
    $api_user = Helper::get_option('dwp_auth_username');
    $api_pwd = Helper::get_option('dwp_auth_password');
    
    $xml_data = '';
    return $xml_data;
  }

  public static function dwp_sim_check_xml5()
  {
    $api_user = Helper::get_option('dwp_auth_username');
    $api_pwd = Helper::get_option('dwp_auth_password');
    
    $xml_data = '';
    return $xml_data;
  }

  public static function dwp_response_handler()
  {
    $xml_data = '<?xml version="1.0"?>
    <Response id="8a4b3c67e0f943cdebe0f54ce8a8e88d">
      <status no="0"/>
      <block name="disconnections">
        <block>
          <a name="disconnection-date" format="date">2015-09-01 10:10:00</a>
          <a name="disconnection-request-id" format="counting">43534</a>
          <a name="mobile-number" format="phone">076477553458</a>
          <a name="pac" format="text">owl</a>
          <a name="port-out-date" format="date">2015-04-06</a>
          <a name="status" format="text">purple</a>
        </block>
        <block>
          <a name="disconnection-date" format="date">2015-12-24</a>
          <a name="disconnection-request-id" format="counting">38183</a>
          <a name="mobile-number" format="phone">073876820411</a>
          <a name="pac" format="text">hound</a>
          <a name="port-out-date" format="date">2015-08-01</a>
          <a name="status" format="text">coyote</a>
        </block>
      </block>
    </Response>';

    libxml_use_internal_errors(true);
    // $xml_data = str_replace(array("\n", "\r", "\t"), '', $xml_data);
    // $xml_data = trim(str_replace('"', "'", $xml_data));
    $xml = simplexml_load_string($xml_data);
    if(!$xml){
      return array('fault'=>1);
    }

    $response = json_decode(json_encode($xml), true);

    return json_decode(json_encode($xml), true);
  }
} 
?>