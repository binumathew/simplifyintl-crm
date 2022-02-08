<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use TelnaService;

class TelnaController extends Controller
{
    public function get_sim_info(Request $request){
    	$TelnaService = new TelnaService;
        $getsiminfo   = $TelnaService->get_sim_info(
                            '8910300000003050901'
                        );
        dd($getsiminfo);
    }
}
