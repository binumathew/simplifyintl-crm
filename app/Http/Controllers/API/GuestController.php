<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

use DB;
use Utils;
use Log;
use Carbon;

use App\Models\SimStock;
use App\Models\Sale;
use App\Models\AutoPlan;

class GuestController extends Controller
{
	public function __construct()
	{
		$this->middleware('guest');
	}

	/*save Esim stock*/
	public function save_esim_stock(Request $request)
	{
		try{
			// $request->merge([
   //              'ssn' => !is_null($request->ssn) ? Utils::simNo($request->ssn) : null
   //          ]);

			$validator = Validator::make($request->all(), [
	            'ssn'   =>  'required|string|unique:tbl_sim_stock,sim_number',
	            'qr'   =>  'required|string',
	            'provider'=>'required|string'
			]);
			if ($validator->fails()) {
	            return $this->return400($validator->errors());
	        }
	        $result = SimStock::create($request);
	        if($result === false){
                return $this->return400();
            }
            return $this->returnSuccess(['message' => 'stock added successfully!!']);
		}catch(\Exception $e){
			Log::error('save_esim_stock',[
                'request' => json_encode($request->all),
                'error' =>   $e->getMessage()
            ]);
            return $this->return400();
		}
	}
}