<?php
namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\GlobalSim;
use Log;
use Utils;

class UserController extends Controller
{

    public function __construct()
    {

    }

	public function handleWebhook(Request $request)
	{
            try{
                // Log::info('globalsim-webhook',[
                //     'all'  =>  $request->all(),
                //     'content' => $request->getContent(),
                //     'parsed' => GlobalSim::webhookContent($request->getContent()),
                //     'method' => $request->method(),
                //     'query' => $request->query()
                // ]);

                return [
                    'result' => true
                ];
            }catch(\Exception $e){
                return [
                    'result'    => false,
                    'error' =>  $e->getMessage()
                ];
            }
    }
}


