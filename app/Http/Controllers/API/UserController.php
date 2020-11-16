<?php
namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use Log;

class UserController extends Controller
{

    public function __construct()
    {

    }

	public function test()
	{
            try{
                Log::info('An informational message.');
                return [
                    'result' => true
                ];
            }catch(\Exception $e){
                dd($e);
                return [
                    'result'    => false,
                    'error' =>  $e->getMessage()
                ];
            }
	}
}


