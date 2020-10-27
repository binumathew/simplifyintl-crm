<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DwpHelper;

class DwpController extends Controller
{
    /**
    * DWP .     
    * @return sim check pack
    */
    public function dwp_sim_check(Request $request)
    {
        $html = DwpHelper::testresponse();

        $dom = new \DOMDocument();
        $dom->loadXML($html);
        $response = json_encode($this->element_to_obj($dom->documentElement));
        $response = json_decode($response);
        print_r($response);
        die();


    	$checksim = DwpHelper::dwp_check_sim_xml(1);
    	$request  = DwpHelper::dwp_process_api($checksim);
    	$response = json_decode(DwpHelper::dwp_response_handler($request));
    	print_r($response);
    }

    public function element_to_obj($element) {
        $obj = array( "tag" => $element->tagName );
        foreach ($element->attributes as $attribute) {
            $obj[$attribute->name] = $attribute->value;
        }
        foreach ($element->childNodes as $subElement) {
            if ($subElement->nodeType == XML_TEXT_NODE) {
                $obj["html"] = $subElement->wholeText;
            }
            elseif ($subElement->nodeType == XML_CDATA_SECTION_NODE) {
                $obj["html"] = $subElement->data;
            }
            else {
                $obj["children"][] = DwpHelper::element_to_obj($subElement);
            }
        }
        return $obj;
    }

    /**
    * DWP .     
    * @return Check sim 
    */
    public function dwp_check_pac(Request $request)
    {
    	$checkpack = DwpHelper::dwp_check_pac();
    	$request   = DwpHelper::dwp_process_api($checkpack);
    	$response  = json_decode(DwpHelper::dwp_response_handler($request));
    	print_r($response);
    }
    /**
    * DWP .     
    * @return Check order new 
    */
    public function dwp_order_new(Request $request)
    {
    	$ordernew = DwpHelper::dwp_order_new();
    	$request  = DwpHelper::dwp_process_api($ordernew);
    	$response  = json_decode(DwpHelper::dwp_response_handler($request));
    	print_r($response);
    }
    /**
    * DWP .     
    * @return order add product
    */
    public function dwp_order_add_product(Request $request)
    {
        $addproduct = DwpHelper::dwp_order_add_product();
        $request    = DwpHelper::dwp_process_api($addproduct);
        $response   = json_decode(DwpHelper::dwp_response_handler($request));
        print_r($response);
    }
    /**
    * DWP .     
    * @return order provision
    */
    public function dwp_order_provision(Request $request)
    {
        $orderprovision = DwpHelper::dwp_order_provision();
        $request        = DwpHelper::dwp_process_api($orderprovision);
        $response       = json_decode(DwpHelper::dwp_response_handler($request));
        print_r($response);
    }
    /**
    * DWP .     
    * @return order search
    */
    public function dwp_order_search(Request $request)
    {
        $ordersearch    = DwpHelper::dwp_order_search();
        $request        = DwpHelper::dwp_process_api($ordersearch);
        $response       = json_decode(DwpHelper::dwp_response_handler($request));
        //print_r($response);
    }

}
