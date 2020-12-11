<?php
namespace App\Http\Controllers\Webhook;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\GlobalSim;
use Log;
use Utils;

class WebhookController extends Controller
{

    public function __construct(){

    }

    public function globalsim(Request $request){
        return [
            'result' => true
        ];
    }

    public function stripe(Request $request){

        return [
            'result' => true
        ];

    }
}


