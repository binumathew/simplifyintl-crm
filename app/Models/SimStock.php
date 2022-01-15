<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Log;

class SimStock extends Model
{
    protected $table = 'tbl_sim_stock';

    protected $fillable = ['phone_number', 'imsi_number', 'sim_number', 'category', 'box_no','provider'];
    
    public $timestamps = true;

    public function list()
    {
        return $this->hasOne('App\Models\SimList','stock_id','id');
    }

    public function network()
    {
        return $this->hasOne('App\Models\Provider','short_code','provider');
    }
    public function stockcode()
    {
        return $this->hasOne('App\Models\SimStockCode','stock_id','id');
    }
    public static function create($request){
        try{
            DB::beginTransaction();

                $phone_number = self::Query()
                                ->where('verified', 0)
                                ->orderBy('id','desc')
                                ->value('phone_number');

                $dummy_number = ($phone_number) ? ( $phone_number + 1 ):'447590000000';
            
                $stock_id = self::insertGetId([
                    'phone_number' => $dummy_number,
                    'sim_number'  => trim($request->ssn),
                    'imsi_number' => trim($request->ssn),
                    'category' => 'normal',
                    'price' =>'0.00',
                    'status' => 1,
                    'dealer_id'=> 1001,
                    'box_no'=> 'B1/1',
                    'verified'=>0,
                    'provider'=> trim($request->provider),
                    'is_esim'=> 1 
                ]);
                SimStockCode::insertGetId([
                    'stock_id'=> $stock_id,
                    'qr_code'=>trim($request->qr)
                ]);

            DB::commit();

            return true;

        }catch(\Exception $e){
            dd($e->getMessage());
            DB::rollback();
            Log::error($e->getMessage());
            return false;
        }
    }
}
