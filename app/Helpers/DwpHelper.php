<?php 

namespace App\Helpers;
use Helper;
use SoapClient;
use Session;
use Carbon;

class DwpHelper

{

	public static function dwp_process_api($xml_data)

	{

	    // $end_point = Helper::get_option('dwp_api_endpoint'); //'https://onramp-api.daisywholesale.com'; 

	    // $end_point = 'https://api.daisywholesale.com';

	  

	    // $ch = curl_init($end_point);

	    // // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

	    // // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

	    // curl_setopt($ch, CURLOPT_POST, 1);

	    // // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));

	    // curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml_data");

	    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	    // $output = curl_exec($ch);

	    // curl_close($ch);

	    // return $output;

	    return DwpHelper::testresponse();

	}



	public static function dwp_response_handler($xml_data)

	{

  		return json_encode(DwpHelper::html_to_obj($xml_data), JSON_PRETTY_PRINT);

	    // libxml_use_internal_errors(true);

	    // $xml = simplexml_load_string($xml_data);

	    // if(!$xml){

	    //   return array('fault'=>1);

	    // }



	    // $response = json_decode(json_encode($xml), true);



	    // return json_decode(json_encode($xml), true);

  }



  public static function dwp_check_sim_xml($data)

  {

	    $api_user = Helper::get_option('dwp_auth_username');

	    $api_pwd  = Helper::get_option('dwp_auth_password');



	    $xml_data = '<?xml version="1.0"?>

  		<Request module="dwapi" call="mobile_check_sim" id="'.Helper::unique_code(32).'" version="1.0">

  		  <block name="auth">

  		    <a name="username" format="text">'. $api_user .'</a>

  		    <a name="password" format="password">'. $api_pwd .'</a>

  		    <a name="client-id" format="text">1</a>

  		  </block>

  		  <a name="network" format="text">'.$data['network'].'</a>

  		  <a name="sim-serial" format="text">'.$data['sim_number'].'</a>		  

  		</Request>';



	    return $xml_data;

  }

  public static function dwp_check_pac($data)

  {

	    $api_user = Helper::get_option('dwp_auth_username');

	    $api_pwd  = Helper::get_option('dwp_auth_password');



	    $xml_data = '<?xml version="1.0"?>

  		<Request module="dwapi" call="mobile_check_pac" id="'.Helper::unique_code(32).'" version="1.0">

  		  <block name="auth">

  		    <a name="username" format="text">'. $api_user .'</a>

  		    <a name="password" format="password">'. $api_pwd .'</a>

  		    <a name="client-id" format="text">1</a>

  		  </block>

  		    <a name="mobile-number" format="phone">'.$data['cli'].'</a>

    			<a name="pac" format="text">'.$data['pac_code'].'</a>

  		</Request>';



	    return $xml_data;

	}



	public static function dwp_order_new($data)

	{

	    $api_user = Helper::get_option('dwp_auth_username');

	    $api_pwd  = Helper::get_option('dwp_auth_password');



	    $xml_data = '<?xml version="1.0"?>

  		<Request module="dwapi" call="mobile_order_new" id="'.Helper::unique_code(32).'" version="1.0">

  		  <block name="auth">

  		    <a name="username" format="text">'. $api_user .'</a>

  		    <a name="password" format="password">'. $api_pwd .'</a>

  		    <a name="client-id" format="text">1</a>

  		  </block>

  		  <a name="assignto-username" format="text"></a>

  			<a name="customer-purchase-order-reference" format="text">'.$data['order_id'].'</a>

  			<a name="name" format="text">'.$data['assign'].'</a>

  		</Request>';



	    return $xml_data;

	}

  public static function dwp_order_add_product($data)

  {

  		$api_user = Helper::get_option('dwp_auth_username');

	    $api_pwd  = Helper::get_option('dwp_auth_password');



	    $xml_data = '<?xml version="1.0"?>

  		<Request module="dwapi" call="mobile_order_add_product" id="'.Helper::unique_code(32).'" version="1.0">

  		  <block name="auth">

  		    <a name="username" format="text">'. $api_user .'</a>

  		    <a name="password" format="password">'. $api_pwd .'</a>

  		    <a name="client-id" format="text">1</a>

  		  </block>

  		  <block name="mobile">

  		    <a name="acquisition-method" format="text">'.$data['type'].'</a>

          <a name="bill-limit" format="counting">'.$data['bill_limit'].'</a>

  		    <a name="is-sim-required" format="boolean">1</a>  		    

  		    <a name="order-id" format="counting">'.$data['order_id'].'</a>

          <a name="sim-buffer-serial" format="text">'.$data['sim_number'].'</a>

          <a name="sim-is-buffer" format="text">1</a>

          <a name="sim-type" format="text">triple</a>

          <a name="use-billing-address" format="boolean">1</a>

          <a name="user-name" format="text">'.$data['user_name'].'</a>

          ';

      if($data['type'] == 'port'){

        $xml_data .= '<a name="mobile-number" format="phone">'.$data['port_cli'].'</a>

  		    <a name="pac" format="text">'.$data['pac_code'].'</a>

          <a name="port-date" format="text">'.$data['port_date'].'</a>

          ';          

          // <a name="port-date" format="text">dog</a>

      }else{

        $xml_data .= '<a name="activation-date" format="date">'.$data['activation'].'</a>

        ';

      }

      if($data['network'] == 'O2'){

        $xml_data .= '<a name="bar-51" format="boolean">1</a>        

          <a name="bar-60" format="boolean">1</a>

          <a name="w" format="boolean">1</a>

          <a name="ab" format="boolean">1</a>

          ';

          // <a name="M" format="boolean">1</a>        

          // <a name="sb=0003" format="boolean">1</a>

          // <a name="bar-61 w" format="boolean">1</a>

          // <a name="bar-66 " format="boolean">1</a>

          // <a name="sb=0001" format="boolean">1</a>

          // <a name="L" format="boolean">1</a>

          // <a name="I" format="boolean">1</a>

      }elseif($data['network'] == 'Vodafone'){

        $xml_data .= '<a name="bar-36" format="boolean">1</a>

          <a name="bar-39" format="boolean">1</a>

          <a name="bar-43" format="boolean">1</a>

          <a name="bar-44" format="boolean">1</a>

          <a name="bar-45" format="boolean">1</a>

          ';

      }

  		$xml_data .= '<a name="product-id" format="counting">'.$data['product_id'].'</a>

  		    <a name="wwcap-enabled" format="boolean">1</a>

  		  </block>

  		  <a name="order-id" format="counting">'.$data['order_id'].'</a> 		  

  		  <a name="product-type" format="text">voice</a>

  		</Request>';



	    return $xml_data;

	}



	public static function dwp_order_provision($data)

	{

	    $api_user = Helper::get_option('dwp_auth_username');

	    $api_pwd  = Helper::get_option('dwp_auth_password');



	    $xml_data = '<?xml version="1.0"?>

  		<Request module="dwapi" call="mobile_order_provision" id="'.Helper::unique_code(32).'" version="1.0">

  		  <block name="auth">

  		    <a name="username" format="text">'. $api_user .'</a>

  		    <a name="password" format="password">'. $api_pwd .'</a>

  		    <a name="client-id" format="text">1</a>

  		  </block>

  		    <a name="order-id" format="counting">'.$data['order_id'].'</a>

  		</Request>';



	    return $xml_data;

	}



  public static function dwp_check_mobile_bars_xml($data)

  {

    $api_user = Helper::get_option('dwp_auth_username');

    $api_pwd  = Helper::get_option('dwp_auth_password');



    $xml_data = '<?xml version="1.0"?>

    <Request module="dwapi" call="mobile_bars" id="'.Helper::unique_code(32).'" version="1.0">

      <block name="auth">

        <a name="username" format="text">'. $api_user .'</a>

        <a name="password" format="password">'. $api_pwd .'</a>

        <a name="client-id" format="text">1</a>

      </block>          

      <a name="mobile-number" format="phone">'.$data['cli'].'</a>

      <a name="refresh" format="boolean">0</a>         

    </Request>';



    return $xml_data;

  }



	public static function dwp_order_search($data)

	{

    $api_user = Helper::get_option('dwp_auth_username');

    $api_pwd  = Helper::get_option('dwp_auth_password');



    $xml_data = '<?xml version="1.0"?>

		<Request module="dwapi" call="mobile_order_search" id="'.$data['order_id'].'" version="1.0">

		  <block name="auth">

		    <a name="username" format="text">'. $api_user .'</a>

		    <a name="password" format="password">'. $api_pwd .'</a>

		    <a name="client-id" format="text">1</a>

		  </block>  		    

		  <a name="id" format="counting">'.$data['order_id'].'</a>  		  	

		</Request>';



    return $xml_data;

	}



  public static function dwp_set_bar($data)

  {

    $api_user = Helper::get_option('dwp_auth_username');

    $api_pwd  = Helper::get_option('dwp_auth_password');



    $xml_data = '<?xml version="1.0"?>

    <Request module="dwapi" call="mobile_set_bars" id="'.Helper::unique_code(32).'" version="1.0">

      <block name="auth">

        <a name="username" format="text">'. $api_user .'</a>

        <a name="password" format="password">'. $api_pwd .'</a>

        <a name="client-id" format="text">1</a>

      </block>

      ';

      if($data['bars_off'] != ''){

        $xml_data .= '<a name="bars-off" format="csv">'.$data['bars_off'].'</a>

          ';

      }

      if($data['bars_on'] != ''){

        $xml_data .= '<a name="bars-on" format="csv">'.$data['bars_on'].'</a>

        ';

      }

      $xml_data .= '<a name="customer-reference" format="text">'.$data['order_id'].'</a>

      <a name="description" format="text">'.$data['order_id'].'</a>

      <a name="mobile-number" format="phone">'.$data['phone'].'</a>       

    </Request>';



    return $xml_data;

  }



  public static function dwp_set_services($data)

  {

    $api_user = Helper::get_option('dwp_auth_username');

    $api_pwd  = Helper::get_option('dwp_auth_password');



    $xml_data = '<?xml version="1.0"?>

    <Request module="dwapi" call="mobile_set_services" id="'.Helper::unique_code(32).'" version="1.0">

      <block name="auth">

        <a name="username" format="text">'. $api_user .'</a>

        <a name="password" format="password">'. $api_pwd .'</a>

        <a name="client-id" format="text">1</a>

      </block> 

      <a name="customer-reference" format="text">'.$data['order_id'].'</a>

      <a name="mobile-number" format="phone">'.$data['phone'].'</a>         

      <block name="services">';

      foreach($data['services'] as $key => $value){

        $xml_data .= '<block>

          <a name="name" format="text">'. $key .'</a>

          <a name="value" format="text">'. $value.'</a>

        </block>';      

      }



      $xml_data .= '</block>        

    </Request>';



    return $xml_data;

  }



  public static function dwp_set_apns($data)

  {

    $api_user = Helper::get_option('dwp_auth_username');

    $api_pwd  = Helper::get_option('dwp_auth_password');



    $xml_data = '<?xml version="1.0"?>

    <Request module="dwapi" call="mobile_set_apns" id="'.Helper::unique_code(32).'" version="1.0">

      <block name="auth">

        <a name="username" format="text">'. $api_user .'</a>

        <a name="password" format="password">'. $api_pwd .'</a>

        <a name="client-id" format="text">1</a>

      </block>             

      <block name="apns">';

      foreach($data['apns'] as $key => $value){

        $xml_data .= '<block>

          <a name="name" format="text">'. $key .'</a>

          <a name="value" format="text">'. $value.'</a>

        </block>';      

      }

      $xml_data .= '</block>

      <a name="customer-reference" format="text">'.$data['order_id'].'</a>

      <a name="mobile-number" format="phone">'.$data['phone'].'</a>       

    </Request>';



    return $xml_data;

  }

    

  	public static function testresponse(){
      return $xml_data ='<?xml version="1.0"?>
      <Response id="4e14ce7d248e10c3b6df008860226fb8">
        <status no="0"/>
        <block name="orders">
          <block>
            <block name="components">
              <block>
                <a name="acquisition-method" format="text">new</a>
                <a name="appointment-datetime" format="datetime">2015-07-21 18:54:07</a>
                <a name="appointment-slot" format="text">AM</a>
                <a name="completion-date" format="date">2015-09-09</a>
                <a name="component-id" format="counting">30923</a>
                <block name="data">
                  <a name="order-type" format="text">Mobile Connection Request</a>
                  <a name="required-by-date" format="date">2015-11-13</a>
                </block>
                <a name="last-update" format="datetime">2015-12-02 08:44:21</a>
                <a name="mobile-number" format="phone">070016668558</a>
                <a name="port-date" format="datetime">2015-09-03 20:23:54</a>
                <a name="rejection-reason" format="text">Lorem ipsum</a>
                <a name="sim-serial" format="text">89441000300307293021</a>
                <a name="state" format="text">lemur</a>
                <a name="state-reason" format="text">termite</a>
                <a name="status" format="text">In Progress</a>
                <a name="supplier-error-code" format="text">iguana</a>
                <a name="type" format="text">new</a>
                <a name="user-name" format="text">poodle</a>
              </block>
              <block>
                <a name="acquisition-method" format="text">migration</a>
                <a name="appointment-datetime" format="datetime">2015-01-25 15:40:59</a>
                <a name="appointment-slot" format="text">PM</a>
                <a name="completion-date" format="date">2015-02-13</a>
                <a name="component-id" format="counting">12750</a>
                <block name="data">
                  <a name="order-type" format="text">Mobile Connection Request</a>
                  <a name="required-by-date" format="date">2015-12-27</a>
                </block>
                <a name="last-update" format="datetime">2015-02-12 19:11:22</a>
                <a name="mobile-number" format="phone">071843858382</a>
                <a name="port-date" format="datetime">2015-08-08 06:02:29</a>
                <a name="rejection-reason" format="text">crab</a>
                <a name="sim-serial" format="text">puce</a>
                <a name="state" format="text">egret</a>
                <a name="state-reason" format="text">fowl</a>
                <a name="status" format="text">cyan</a>
                <a name="supplier-error-code" format="text">ivory</a>
                <a name="type" format="text">portin</a>
                <a name="user-name" format="text">beaver</a>
              </block>
            </block>
            <a name="customer-reference" format="text">cricket</a>
            <a name="expected-completion-date" format="date">2015-04-08</a>
            <a name="id" format="counting">49105</a>
            <block name="order-details">
              <a name="customer-purchase-number" format="counting">38953</a>
              <a name="order-id" format="counting">65090</a>
              <a name="status" format="text">Complete</a>
            </block>
            <a name="request-stage" format="text">ostrich</a>
            <a name="state" format="text">parrot</a>
            <a name="status" format="text">rooster</a>
          </block>
          <block>
            <block name="components">
              <block>
                <a name="acquisition-method" format="text">raccoon</a>
                <a name="appointment-datetime" format="datetime">2015-11-17 04:27:17</a>
                <a name="appointment-slot" format="text">stork</a>
                <a name="completion-date" format="date">2015-09-24</a>
                <a name="component-id" format="counting">11319</a>
                <block name="data">
                  <a name="order-type" format="text">blackbird</a>
                  <a name="required-by-date" format="date">2015-05-16</a>
                </block>
                <a name="last-update" format="datetime">2015-11-03 09:44:38</a>
                <a name="mobile-number" format="phone">071375423152</a>
                <a name="port-date" format="datetime">2015-06-04 20:39:29</a>
                <a name="rejection-reason" format="text">sepia</a>
                <a name="sim-serial" format="text">anteater</a>
                <a name="state" format="text">dormouse</a>
                <a name="state-reason" format="text">oyster</a>
                <a name="status" format="text">celeste</a>
                <a name="supplier-error-code" format="text">narwhal</a>
                <a name="type" format="text">parakeet</a>
                <a name="user-name" format="text">quail</a>
              </block>
              <block>
                <a name="acquisition-method" format="text">prawn</a>
                <a name="appointment-datetime" format="datetime">2015-08-20 21:11:02</a>
                <a name="appointment-slot" format="text">beetle</a>
                <a name="completion-date" format="date">2015-04-12</a>
                <a name="component-id" format="counting">70648</a>
                <block name="data">
                  <a name="order-type" format="text">arrow</a>
                  <a name="required-by-date" format="date">2015-07-03</a>
                </block>
                <a name="last-update" format="datetime">2015-10-01 22:32:45</a>
                <a name="mobile-number" format="phone">072708610587</a>
                <a name="port-date" format="datetime">2015-06-09 15:08:52</a>
                <a name="rejection-reason" format="text">leopon</a>
                <a name="sim-serial" format="text">tyrannosaurus</a>
                <a name="state" format="text">completed</a>
                <a name="state-reason" format="text">boa</a>
                <a name="status" format="text">prawn</a>
                <a name="supplier-error-code" format="text">pony</a>
                <a name="type" format="text">hound</a>
                <a name="user-name" format="text">pigeon</a>
              </block>
            </block>
            <a name="customer-reference" format="text">worm</a>
            <a name="expected-completion-date" format="date">2015-00-00</a>
            <a name="id" format="counting">6744</a>
            <block name="order-details">
              <a name="customer-purchase-number" format="counting">63877</a>
              <a name="order-id" format="counting">33926</a>
              <a name="status" format="text">warbler</a>
            </block>
            <a name="request-stage" format="text">Complete</a>
            <a name="state" format="text">orca</a>
            <a name="status" format="text">silverfish</a>
          </block>
        </block>
        <block name="pagination">
          <a name="direction" format="text">lizard</a>
          <a name="page" format="counting">46263</a>
          <a name="pages" format="counting">45388</a>
          <a name="sort" format="text">falcon</a>
          <a name="total" format="counting">20380</a>
        </block>
      </Response>';
  		/*$xml_data = '<?xml version="1.0"?>

  <Response id="4e14ce7d248e10c3b6df008860226fb8">

    <status no="0"/>

    <block name="orders">

      <block>

        <block name="components">

          <block>

            <a name="acquisition-method" format="text">new</a>

            <a name="appointment-datetime" format="datetime">2015-07-21 18:54:07</a>

            <a name="appointment-slot" format="text">AM</a>

            <a name="completion-date" format="date">2015-09-09</a>

            <a name="component-id" format="counting">30923</a>

            <block name="data">

              <a name="order-type" format="text">Mobile Connection Request</a>

              <a name="required-by-date" format="date">2015-11-13</a>

            </block>

            <a name="last-update" format="datetime">2015-12-02 08:44:21</a>

            <a name="mobile-number" format="phone">070016668558</a>

            <a name="port-date" format="datetime">2015-09-03 20:23:54</a>

            <a name="rejection-reason" format="text">Lorem ipsum</a>

            <a name="sim-serial" format="text">89441000300307293021</a>

            <a name="state" format="text">lemur</a>

            <a name="state-reason" format="text">termite</a>

            <a name="status" format="text">In Progress</a>

            <a name="supplier-error-code" format="text">iguana</a>

            <a name="type" format="text">new</a>

            <a name="user-name" format="text">poodle</a>

          </block>

          <block>

            <a name="acquisition-method" format="text">migration</a>

            <a name="appointment-datetime" format="datetime">2015-01-25 15:40:59</a>

            <a name="appointment-slot" format="text">PM</a>

            <a name="completion-date" format="date">2015-02-13</a>

            <a name="component-id" format="counting">12750</a>

            <block name="data">

              <a name="order-type" format="text">Mobile Connection Request</a>

              <a name="required-by-date" format="date">2015-12-27</a>

            </block>

            <a name="last-update" format="datetime">2015-02-12 19:11:22</a>

            <a name="mobile-number" format="phone">071843858382</a>

            <a name="port-date" format="datetime">2015-08-08 06:02:29</a>

            <a name="rejection-reason" format="text">crab</a>

            <a name="sim-serial" format="text">puce</a>

            <a name="state" format="text">egret</a>

            <a name="state-reason" format="text">fowl</a>

            <a name="status" format="text">cyan</a>

            <a name="supplier-error-code" format="text">ivory</a>

            <a name="type" format="text">portin</a>

            <a name="user-name" format="text">beaver</a>

          </block>

        </block>

        <a name="customer-reference" format="text">cricket</a>

        <a name="expected-completion-date" format="date">2015-04-08</a>

        <a name="id" format="counting">49105</a>

        <block name="order-details">

          <a name="customer-purchase-number" format="counting">38953</a>

          <a name="order-id" format="counting">65090</a>

          <a name="status" format="text">Complete</a>

        </block>

        <a name="request-stage" format="text">ostrich</a>

        <a name="state" format="text">parrot</a>

        <a name="status" format="text">rooster</a>

      </block>

      <block>

        <block name="components">

          <block>

            <a name="acquisition-method" format="text">raccoon</a>

            <a name="appointment-datetime" format="datetime">2015-11-17 04:27:17</a>

            <a name="appointment-slot" format="text">stork</a>

            <a name="completion-date" format="date">2015-09-24</a>

            <a name="component-id" format="counting">11319</a>

            <block name="data">

              <a name="order-type" format="text">blackbird</a>

              <a name="required-by-date" format="date">2015-05-16</a>

            </block>

            <a name="last-update" format="datetime">2015-11-03 09:44:38</a>

            <a name="mobile-number" format="phone">071375423152</a>

            <a name="port-date" format="datetime">2015-06-04 20:39:29</a>

            <a name="rejection-reason" format="text">sepia</a>

            <a name="sim-serial" format="text">anteater</a>

            <a name="state" format="text">dormouse</a>

            <a name="state-reason" format="text">oyster</a>

            <a name="status" format="text">celeste</a>

            <a name="supplier-error-code" format="text">narwhal</a>

            <a name="type" format="text">parakeet</a>

            <a name="user-name" format="text">quail</a>

          </block>

          <block>

            <a name="acquisition-method" format="text">prawn</a>

            <a name="appointment-datetime" format="datetime">2015-08-20 21:11:02</a>

            <a name="appointment-slot" format="text">beetle</a>

            <a name="completion-date" format="date">2015-04-12</a>

            <a name="component-id" format="counting">70648</a>

            <block name="data">

              <a name="order-type" format="text">arrow</a>

              <a name="required-by-date" format="date">2015-07-03</a>

            </block>

            <a name="last-update" format="datetime">2015-10-01 22:32:45</a>

            <a name="mobile-number" format="phone">072708610587</a>

            <a name="port-date" format="datetime">2015-06-09 15:08:52</a>

            <a name="rejection-reason" format="text">leopon</a>

            <a name="sim-serial" format="text">tyrannosaurus</a>

            <a name="state" format="text">teal</a>

            <a name="state-reason" format="text">boa</a>

            <a name="status" format="text">prawn</a>

            <a name="supplier-error-code" format="text">pony</a>

            <a name="type" format="text">hound</a>

            <a name="user-name" format="text">pigeon</a>

          </block>

        </block>

        <a name="customer-reference" format="text">worm</a>

        <a name="expected-completion-date" format="date">2015-00-00</a>

        <a name="id" format="counting">6744</a>

        <block name="order-details">

          <a name="customer-purchase-number" format="counting">63877</a>

          <a name="order-id" format="counting">33926</a>

          <a name="status" format="text">warbler</a>

        </block>

        <a name="request-stage" format="text">spider</a>

        <a name="state" format="text">orca</a>

        <a name="status" format="text">silverfish</a>

      </block>

    </block>

    <block name="pagination">

      <a name="direction" format="text">lizard</a>

      <a name="page" format="counting">46263</a>

      <a name="pages" format="counting">45388</a>

      <a name="sort" format="text">falcon</a>

      <a name="total" format="counting">20380</a>

    </block>

  </Response>';

      return $xml_data;*/

		 

  	}

  	public static function element_to_obj($element) {

	    $obj = array( "tag" => $element->tagName );

        foreach ($element->attributes as $attribute) {

            $obj[$attribute->name] = $attribute->value;

        }

        foreach ($element->childNodes as $subElement) {

            if ($subElement->nodeType == XML_TEXT_NODE) {

                $obj["html"] = $subElement->wholeText;

            } elseif ($subElement->nodeType == XML_CDATA_SECTION_NODE) {

                $obj["html"] = $subElement->data;

            } else {

                $obj["children"][] = DwpHelper::element_to_obj($subElement);

            }

        }

        return $obj;

	}

  	public static function html_to_obj($html) {

	    $dom = new \DOMDocument();

	    $dom->loadXML($html);

	    return DwpHelper::element_to_obj($dom->documentElement);

	}
  public static function dwp_response($children)
  {
    foreach($children as $details){
        if(isset($details->children)){
            $key = (isset($details->name))?$details->name:((isset($details->tag))?$details->tag:((isset($details->id))?$details->id:'key'));
            $dwp[$key] = DwpHelper::dwp_response($details->children);
        }else{
            $key = (isset($details->name))?$details->name:((isset($details->tag))?$details->tag:((isset($details->id))?$details->id:'key'));
            $dwp[$key] = (isset($details->html))?$details->html:$details;
        }
    }
    return $dwp;
  }

  public static function initiate_soap_client() {

    $WSDL_uri   = "https://api.affinity.akjl.co.uk/gencom/AffinityAPIService.svc?WSDL";
      $options  = [
              'cache_wsdl'     => WSDL_CACHE_NONE,
              'trace'          => 1,
              'stream_context' => stream_context_create(
                  [
                      'ssl' => [
                          'verify_peer'       => false,
                          'verify_peer_name'  => false,
                          'allow_self_signed' => true
                      ]
                  ]
              )
          ];

    try{
      $SOAPClient = new SoapClient($WSDL_uri, $options);
    }
    catch(\Exception $e) {
      return false;
    }
    

    $res = new \stdClass;

    if(!Session::has('affinityToken')){
      $token = DwpHelper::generate_identity_token($SOAPClient);
        if($token != false){
            Session::put('affinityToken',$token);
            Session::save();
            $res->token   = $token;
        }else{ $res->token = false; }
    }else{
      $token        = Session::get('affinityToken');
      $res->token   = $token;
    }
    $res->SOAPClient = $SOAPClient; 
    return $res;
  }

  public static function generate_identity_token($SOAPClient){

      try {
          $loginResult = $SOAPClient->GetIdentityToken(array("UserName" => "APIUser", "Password" => "&g%3r{HxqQ;yT[Q4", "ApplicationName" => "PHP Script", "ApplicationReference" => "PHP Test"));
        if ($loginResult){

          if ($loginResult->GetIdentityTokenResult->ResponseCode == 0){

            return $session = $loginResult->GetIdentityTokenResult->IdentityToken;
            
          }
        }
      } catch (Exception $e) {
            //echo 'Caught exception: ', $e->getMessage(), "\n";
        return false;
      }
  }
  public static function load_from_xml($xmldata){
    
    $xml = preg_replace('/(<\/?)(\w+):([^>]*>)/', '$1$2$3', $xmldata);
    $xml = simplexml_load_string($xml);
    $json = json_encode($xml);
    return $responseArray = json_decode($json);
  }
  public static function create_new_site($client,$user){
    if($client->token){
        $obj         = new \stdClass();
        $obj->status = 400;

        $SOAPClient     = $client->SOAPClient;
        $token          = $client->token;

        $firstname = ($user->first_name == "") ? $user->parent->first_name : $user->first_name;
        $lastname = ($user->last_name == "") ? $user->parent->last_name : $user->last_name;

        $createNewSite = $SOAPClient->CreateNewSiteByID(array("IdentityToken" => $token, "siteName" => $firstname.' '.$lastname, "siteRef" => "AVO".str_pad($user->id, 4, '0', STR_PAD_LEFT), "dealerID" => -1,'companyID'=>7));

      if($createNewSite){
        if($createNewSite->CreateNewSiteByIDResult->ResponseCode == 0){
          $siteId = $createNewSite->siteID;
          $obj->status  = 200;
          $obj->siteId  = $siteId; 
          return $obj;
        }else{
          $obj->error  = json_encode($createNewSite);
          return $obj;
        }
      }
    }
  }
public static function add_cli($client,$siteId,$cli){
  if($client->token){
    $obj         = new \stdClass();
    $obj->status = 400;

    $SOAPClient = $client->SOAPClient;
    $token      = $client->token;

    $startDate = Carbon::now()->format('d/m/Y');
    $endDate   = Carbon::now()->addmonth(1)->format('d/m/Y');
    
    $addCli = $SOAPClient->CreateNewCLI(['IdentityToken'=>$token,'SiteID'=>$siteId,'CLINumber'=>$cli,'TerminatingNumber'=>'','CLIType'=>"MOBILE",'StartDate'=>$startDate,'EndDate'=>$endDate]);

    if($addCli){
     if($addCli->CreateNewCLIResult->ResponseCode == 0){
       $newCli = $addCli->CreateNewCLIResult->Result;
        $obj->status  = 200;
        $obj->newCli  = $newCli; 
        return $obj;
     }else{ 
      $obj->error = $addCli->CreateNewCLIResult->ResponseMessage;
      return $obj;}
    }
  }
}
public static function update_cli_details($client,$cliID,$details){
  if($client->token){
        $SOAPClient     = $client->SOAPClient;
        $token      = $client->token;
        return $updatecli = $SOAPClient->UpdateCLIDetailsByID(['IdentityToken'=>$token,'cliID'=>$cliID,'cliDetails'=>$details]);
        if($updatecli){
        if($updatecli->UpdateCLIDetailsByIDResult->ResponseCode == 0){
          print_r($updatecli);
        }
    }
  }
}



}