<?php
namespace App\Http\Controllers\API;  

use Auth;
use App\User;

class UserController extends Controller 
{
    /**
    * Create a new controller instance.
    * @return void
    */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

	/** 
	* details api 
	* @return \Illuminate\Http\Response 
	*/ 
	public function get_user() 
	{  				    
        return response()->json(['success' => Auth::user()]); 
	}
}


