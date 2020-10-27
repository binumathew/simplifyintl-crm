<?php 
namespace App\Helpers;

use Crypt;
use Helper;
use Carbon;


class SwitchHelper
{
  public static function call_switch_api($xml_data, $server = '33')
  {
    if($server == '33'){
      $end_point = Helper::get_option('call_switch_end_point');
      $api_userpwd = Helper::get_option('call_switch_api_pass');
    }else{
      $end_point = 'https://149.36.7.35/xmlapi/xmlapi';
      $api_userpwd = Helper::get_option('call_free0870_api_pass');
    }
    // $api_userpwd = Crypt::decrypt($api_userpwd);
    $ch = curl_init($end_point);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml_data");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, "$api_userpwd");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    $output = curl_exec($ch);       
    curl_close($ch);
 
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($output);
    if(!$xml){
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
    return $temp;         
  }

  public static function switch_create_account_xml($data)
  {
    $xml_data = '<?xml version="1.0"?>
      <methodCall>
        <methodName>createAccount</methodName>
        <params>
          <param>
            <value>
              <struct>
                <member>
                  <name>username</name>
                  <value><string>'. $data['username'] .'</string></value>
                </member>
                <member>
                  <name>account_class</name>
                  <value><int>'. $data['a_class'] .'</int></value>
                </member>
                <member>
                  <name>web_password</name>
                  <value><string>'. $data['username'] .'</string></value>
                </member>
                <member>
                  <name>authname</name>
                  <value><string>'. $data['username'] .'</string></value>
                </member>
                <member>
                  <name>voip_password</name>
                  <value><string>'. $data['vp_password'] .'</string></value>
                </member>
                <member>
                  <name>i_tariff</name>
                  <value><int>'. $data['tariff'] .'</int></value>
                </member>
                <member>
                  <name>i_time_zone</name>
                  <value><int>'. $data['timezone_value'] .'</int></value>
                </member>
                <member>
                <name>i_lang</name><value><string>en</string></value>
                </member>
                <member>
                  <name>balance</name>
                  <value><double>'. number_format($data['balance'],2) .'</double></value>
                </member>
                <member>
                  <name>credit_limit</name><value><double>0.0</double></value>
                </member>
                <member>
                  <name>blocked</name><value><int>0</int></value>
                </member>
                <member>
                  <name>max_sessions</name><value><int>2</int></value>
                </member>
                <member>
                  <name>max_credit_time</name><value><int>3600</int></value>
                </member>
                <member>
                  <name>translation_rule</name>
                  <value><string>'. $data['translation_rule'] .' </string></value>
                </member>
                <member>
                  <name>cli_translation_rule</name>
                  <value><string></string></value>
                </member>
                <member>
                  <name>cpe_number</name><value><string></string></value>
                </member>
                <member>
                  <name>i_export_type</name>
                  <value><int>'. $data['export_type'] .'</int></value>
                </member>
                <member>
                  <name>reg_allowed</name><value><int>0</int></value>
                </member>
                <member>
                  <name>trust_cli</name><value><int>1</int></value>
                </member>
                <member>
                  <name>disallow_loops</name><value><int>0</int></value>
                </member>
                <member>
                  <name>vm_password</name>
                  <value><string>'. $data['vm_password'] .'</string></value>
                </member>
                <member>
                  <name>vm_enabled</name><value><int>1</int></value>
                </member>
                <member>
                  <name>vm_notify_emails</name>
                  <value><string>'.$data['notify_email'].'</string></value>
                </member>
                <member>
                  <name>vm_forward_emails</name><value><string></string></value>
                </member>
                <member>
                  <name>vm_del_after_fwd</name><value><int>1</int></value>
                </member>
                <member>
                  <name>company_name</name>
                  <value><string>'. $data['company_name'] .'</string></value>
                </member>
                <member>
                  <name>salutation</name><value><string></string></value>
                </member>
                <member>
                  <name>first_name</name>
                  <value><string>'. $data['first_name'] .'</string></value>
                </member>
                <member>
                  <name>mid_init</name><value><string></string></value>
                </member>
                <member>
                  <name>last_name</name>
                  <value><string>'. $data['last_name'] .'</string></value>
                </member>
                <member>
                  <name>street_addr</name><value><string></string></value>
                </member>
                <member>
                  <name>state</name><value><string></string></value>
                </member>
                <member>
                  <name>postal_code</name><value><string></string></value>
                </member>
                <member>
                  <name>city</name><value><string></string></value>
                </member>
                <member>
                  <name>country</name><value><string></string></value>
                </member>
                <member>
                  <name>contact</name><value><string></string></value>
                </member>
                <member>
                  <name>phone</name>
                  <value><string>'. $data['phone'] .'</string></value>
                </member>
                <member>
                  <name>fax</name><value><string></string></value>
                </member>
                <member>
                  <name>alt_phone</name><value><string></string></value>
                </member>
                <member>
                  <name>alt_contact</name><value><string></string></value>
                </member>
                <member>
                  <name>email</name>
                  <value><string></string></value>
                </member>
                <member>
                  <name>cc</name><value><string></string></value>
                </member>
                <member>
                  <name>bcc</name><value><string></string></value>
                </member>
                <member>
                  <name>payment_currency</name>
                  <value><string>'. $data['currency'] .'</string></value>
                </member>
                <member>
                  <name>payment_method</name><value><int>1</int></value>
                </member>
                <member>
                  <name>on_payment_action</name><value><int>0</int></value>
                </member>
                <member>
                  <name>min_payment_amount</name><value><double>0</double></value>
                </member>
                <member>
                  <name>lifetime</name><value><int>-1</int></value>
                </member>
                <member>
                  <name>preferred_codec</name><value><int>0</int></value>
                </member>
                <member>
                  <name>use_preferred_codec_only</name><value><int>0</int></value>
                </member>
                <member>
                  <name>welcome_call_ivr</name><value><int>0</int></value>
                </member>
                <member>
                  <name>i_billing_plan</name>
                  <value><int>'. $data['billing_plan'] .'</int></value>
                </member>
                <member>
                  <name>i_media_relay_type</name><value><int>0</int></value>
                </member>
                <member>
                  <name>i_password_policy</name><value><int>1</int></value>
                </member>
                <member>
                  <name>i_routing_group</name>
                  <value><int>'. $data['routing_group'] .'</int></value>
                </member>
              </struct>
            </value>
          </param>
        </params>
      </methodCall>';

    return $xml_data;
  }

  public static function switch_update_profile_xml($data)
  {
    $xml_data = '<?xml version="1.0"?>      
    <methodCall>
      <methodName>updateAccount</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>i_account</name>
                <value><int>'. $data['i_account'] .'</int></value>
              </member>
              <member>
                <name>first_name</name>
                <value><string>'. $data['first_name'] .'</string></value>
              </member>
              <member>
                <name>last_name</name>
                <value><string>'. $data['last_name'] .'</string></value>
              </member>
              <member>
                <name>email</name>
                <value><string></string></value>
              </member>
              <member>
                <name>street_addr</name>
                <value><string>'. $data['street_addr'] .'</string></value>
              </member>
              <member>
                <name>state</name>
                <value><string>'. $data['state'] .'</string></value>
              </member>
              <member>
                <name>postal_code</name>
                <value><string>'. $data['postal_code'] .'</string></value>
              </member>
              <member>
                <name>city</name>
                <value><string>'. $data['city'] .'</string></value>
              </member>
              <member>
                <name>country</name>
                <value><string>'. $data['country'] .'</string></value>
              </member>
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data;
  }

  public static function switch_custom_profile_xml($data)
  {
    $xml_data = '<?xml version="1.0"?>      
    <methodCall>
      <methodName>updateAccount</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>i_account</name>
                <value><int>'. $data['i_account'] .'</int></value>
              </member>
              <member>
                <name>reg_allowed</name><value><int>0</int></value>
              </member>
              <member>
                <name>voip_password</name>
                <value><string>'. $data['vp_password'] .'</string></value>
              </member>
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data;
  }           

  public static function switch_change_default_cli_xml($data)
  {
    $xml_data = '<?xml version="1.0"?>      
    <methodCall>
      <methodName>updateAccount</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>i_account</name>
                <value><int>' . $data['i_account'] . '</int></value>
              </member>              
              <member>
                <name>cli_translation_rule</name>
                <value><string>'. $data['cli_number'] .'</string></value>
              </member>
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data;
  }

  public static function switch_update_plan_xml($i_account, $i_billing_plan)
  {
    $xml_data = '<?xml version="1.0"?>
    <methodCall>
      <methodName>updateAccount</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>i_account</name><value><int>'. $i_account .'</int></value>
              </member>
              <member>
                <name>i_billing_plan</name><value><int>'. $i_billing_plan .'</int></value>
              </member>
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data;
  }

  public static function switch_make_call_xml($authname, $cld_first, $cld_second)
  {
    $xml_data = '<?xml version="1.0"?>
    <methodCall>
      <methodName>make2WayCallback</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>authname</name><value><string>'. $authname .'</string></value>
              </member>
              <member>
                <name>cli_second</name><value><string>'. $cld_first .'</string></value>
              </member>
              <member>
                <name>cld_first</name><value><string>'. $cld_first .'</string></value>
              </member>
              <member>
                <name>cld_second</name><value><string>'. $cld_second .'</string></value>
              </member>
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data;
  }

  public static function switch_cancel_call_xml($call_id)
  {
    $xml_data = '<?xml version="1.0"?>
    <methodCall>
      <methodName>cancelCallback</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>i_callback_request</name><value><int>'.$call_id.'</int></value>
              </member>
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data; 
  }

  public static function switch_call_status_xml($i_account='', $start_date='',$end_date='')
  {
    if($start_date){
      $start_date = Carbon::parse($start_date)->format('00:00:01.000 \G\M\T D M d Y');
    } else {
      $start_date = Carbon::now()->format('00:00:01.000 \G\M\T D M d Y');
    }

    if($end_date){
      $end_date = Carbon::parse($end_date)->format('23:59:59.000 \G\M\T D M d Y');
    } else {
      $end_date = Carbon::now()->format('23:59:59.000 \G\M\T D M d Y');
    }

    $xml_data = '<?xml version="1.0"?>
    <methodCall>
      <methodName>getAccountCDRs</methodName>
      <params>
        <param>
          <value>
            <struct>';
              if($i_account){
                $xml_data .= '<member>
                  <name>i_account</name>
                  <value><int>'. $i_account .'</int></value>
                </member>';
              }
              $xml_data .= '<member>
                <name>start_date</name>
                <value>
                  <string>'. $start_date .'</string>
                </value>
              </member>
              <member>
                <name>end_date</name>
                <value>
                  <string>'. $end_date .'</string>
                </value>
              </member>
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data; 
  }

  public static function switch_account_bal_xml($method, $i_account, $amount, $currency, $note='')
  {
    $xml_data = '<?xml version="1.0"?>
    <methodCall>
      <methodName>'. $method .'</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>i_account</name><value><int>'. $i_account .'</int></value>
              </member>
              <member>
                <name>amount</name><value><double>'. $amount .'</double></value>
              </member>
              <member>
                <name>currency</name><value><string>'.$currency.'</string></value>
              </member>
              <member>
                <name>payment_notes</name><value><string>'.$note.'</string></value>
              </member> 
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data; 
  }

  public static function switch_add_cli_xml($i_account, $cli_number)
  {
    $xml_data = '<?xml version="1.0"?>
    <methodCall>
      <methodName>addCLIMapping</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>i_account</name><value><int>'. $i_account .'</int></value>
              </member>
              <member>
                <name>cli</name><value><string>'. $cli_number .'</string></value>
              </member>
              <member>
                <name>lang</name><value><string>en</string></value>
              </member>
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data; 
  }

  public static function switch_delete_cli_xml($i_account, $cli_number)
  {
    $xml_data = '<?xml version="1.0"?>
    <methodCall>
      <methodName>delCLIMapping</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>i_account</name><value><int>'. $i_account .'</int></value>
              </member>
              <member>
                <name>cli</name><value><string>'. $cli_number .'</string></value>
              </member>              
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data; 
  }

  public static function switch_add_auth_rule_xml($data)
  {
    $xml_data = '<?xml version="1.0"?>
    <methodCall>
      <methodName>addAuthRule</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>i_account</name>
                <value><int>'. $data['i_account'] .'</int></value>
              </member>
              <member>
                <name>i_protocol</name><value><int>1</int></value>
              </member>
              <member>
                <name>remote_ip</name>
                <value><string>'. $data['remote_ip'] .'</string></value>
              </member>';
              if(isset($data['cli_number'])){
                $xml_data .= '<member>
                  <name>incoming_cli</name>
                  <value><string>'. $data['cli_number'] .'</string></value>
                </member>';
              }
              if(isset($data['cld_number'])){
                $xml_data .= '<member>
                  <name>incoming_cld</name>
                  <value><string>'. $data['cld_number'] .'</string></value>
                </member>';
              }
              if(isset($data['cli_translation'])){
                $xml_data .= '<member>
                  <name>cli_translation_rule</name>
                  <value><string>'. $data['cli_translation'] .'</string></value>
                </member>';
              }
              if(isset($data['cld_translation'])){
                $xml_data .= '<member>
                  <name>cld_translation_rule</name>
                  <value><string>'. $data['cld_translation'] .'</string></value>
                </member>';
              }
            $xml_data .= '</struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data; 
  }
  
  public static function switch_hotdial_xml($method, $i_account, $hot_key, $dest,$descr='')
  {
    $xml_data = '<?xml version="1.0"?>
    <methodCall>
      <methodName>'. $method .'</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>i_account</name><value><int>'.$i_account.'</int></value>
              </member>
              <member>
                <name>hot_key</name><value><string>'.$hot_key.'</string></value>
              </member>
              <member>
                <name>dest</name><value><string>'.$dest.'</string></value>
              </member> 
              <member>
                <name>description</name><value><string>'.$descr.'</string></value>
              </member>
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data;
  }

  public static function switch_account_status_xml($method, $i_account)
  {
    $xml_data = '<?xml version="1.0"?>
    <methodCall>
      <methodName>'. $method .'</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>i_account</name><value><int>'.$i_account.'</int></value>
              </member>              
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data;
  }  

  public static function switch_delete_hotdial_xml($i_account, $hot_key)
  {
    $xml_data = '<?xml version="1.0"?>
    <methodCall>
      <methodName>delHotDialNumber</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>i_account</name><value><int>'. $i_account .'</int></value>
              </member>
              <member>
                <name>hot_key</name><value><string>'. $hot_key .'</string></value>
              </member>
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data;
  }

  public static function switch_smartdial_xml($method, $i_account, $did, $dest,$desc='')
  {
    $xml_data = '<?xml version="1.0"?>
    <methodCall>
      <methodName>'. $method .'</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>i_account</name><value><string>'. $i_account .'</string></value>
              </member>
              <member>
                <name>did</name><value><string>'. $did .'</string></value>
              </member>
              <member>
                <name>dest</name><value><string>'. $dest .'</string></value>
              </member>
              <member>
                <name>description</name><value><string>'. $desc .'</string></value>
              </member> 
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data;
  }

  public static function switch_delete_smartdial_xml($i_account, $did)
  {
    $xml_data = '<?xml version="1.0"?>
    <methodCall>
      <methodName>deleteSmartDial</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>i_account</name><value><string>'. $i_account .'</string></value>
              </member>
              <member>
                <name>did</name><value><string>'. $did .'</string></value>
              </member>
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data;
  }

  public static function switch_delete_account_xml($i_account)
  {
    $xml_data = '<?xml version="1.0"?>
    <methodCall>
      <methodName>deleteAccount</methodName>
      <params>
        <param>
          <value>
            <struct>
              <member>
                <name>i_account</name><value><int>'.$i_account.'</int></value>
              </member>
            </struct>
          </value>
        </param>
      </params>
    </methodCall>';

    return $xml_data; 
  }

  public static function service_plan_info($plan_id)
  {
    $xml_data = $xml_data = '<?xml version="1.0"?>
      <methodCall>
        <methodName>getServicePlanInfo</methodName>
        <params>
          <param>
            <value>
              <struct>
                <member>
                  <name>i_billing_plan</name><value><int>'. $plan_id .'</int></value>
                </member>
              </struct>
            </value>
          </param>
        </params>
      </methodCall>';

    return $xml_data; 
  }

  public static function conference_bridge_api($xml_data,$sub_url)
  {
    $base_url = Helper::get_option('conference_bridge_end_point');
    $api_userpwd =  Helper::get_option('conference_bridge_pswd');
    $end_point = $base_url . $sub_url;

    $ch = curl_init($end_point);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml_data");
    curl_setopt($ch, CURLOPT_USERPWD, "$api_userpwd");
    $output = curl_exec($ch);
    curl_close($ch);
    $xml=simplexml_load_string($output);
    $json = json_encode($xml);
    $data = json_decode($json,true);
    return $data;         
  }

  public static function conference_bridge($sub_url, $data, $bridge)
  {
      $base_url = Helper::get_bridgeip($bridge);
      $api_url = $base_url.$sub_url;  

      $ch     = curl_init($api_url);   
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));         
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);       
      
      if($errno = curl_errno($ch)) {
        $error_message = curl_strerror($errno);
        echo "cURL error ({$errno}):\n {$error_message}";
      }
      curl_close($ch);
      return json_decode($output,true);    
  }
} 
?>