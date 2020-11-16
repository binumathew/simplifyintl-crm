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


