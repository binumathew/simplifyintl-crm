<?php

namespace App\Models;

use DB;
use Auth;
use Carbon;
use Helper;
use App\Models\Plan;
use App\Models\Boltons;
use App\Models\CartList;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'tbl_cart';

    protected $fillable = ['user_id', 'category', 'category_id', 'amount', 'provider', 'promocode','sim_count','item_count','e_sim'];
    
    public $timestamps = false;

    public function list()
    {   
        // $this->hasMany('App\Models\CartList','cart_id','id')
                // ->where('expire_at','<', Carbon::now())->delete();
        $cart_id = $this->id;
        $is_esim    = $this->is_esim;
        $selected = $this->hasMany('App\Models\CartList','cart_id','id')->get();
        $expire_at = Carbon::now()->addMinutes(20)->format('Y-m-d H:i:s');
        
        $provider = $this->product->provider;
        $dealer = (Auth::user()->role == 5)?Auth::id():1001;
        $box_category = json_decode(Helper::get_option('sim_stock_box_category'), true);
        $box_no = $box_category[$provider];
        foreach ($selected as $list) {            
            if($list->expire_at < Carbon::now()){
                $query = DB::table('tbl_sim_stock')->select('id')
                                ->whereNOTIn('id', function($query){
                                     $query->select('stock_id')->from('tbl_cart_details');
                                })->inRandomOrder()->where('category', 'normal')
                                ->where('dealer_id', $dealer);
                                if($dealer == 1){
                                    $query->where('box_no', $box_no);
                                }
                $auto = $query->where('provider', $provider)
                            ->where('status', 1)
                            ->where('is_esim',$is_esim)->first();
                if($auto){
                    CartList::where('id', $list->id)
                        ->update(['stock_id' => $auto->id, 'expire_at' => $expire_at]);  
                }else{                    
                    CartList::where('cart_id', $cart_id)->delete();
                    $this->destroy($cart_id);
                }
            }
        }

        $sim_count = $this->hasMany('App\Models\CartList','cart_id','id')->count();   
        $total_count = $this->sim_count * $this->item_count;
        if($total_count > $sim_count){
            $limit = $total_count - $sim_count;        
            $created_at = Carbon::now();
            for($i=0;$i<$limit;$i++){
                $query = DB::table('tbl_sim_stock')->select('id')
                            ->whereNOTIn('id', function($query){
                                 $query->select('stock_id')->from('tbl_cart_details');
                            })->inRandomOrder()->where('category', 'normal')
                            ->where('dealer_id', $dealer);
                            if($dealer == 1){
                                $query->where('box_no', $box_no);
                            }
                $auto = $query->where('provider', $provider)
                            ->where('status', 1)
                            ->where('is_esim',$is_esim)->first();
                if($auto){            
                    $reserve = ['cart_id' => $cart_id, 'stock_id' => $auto->id, 
                                'created_at' => $created_at, 'expire_at' => $expire_at];
                    if($total_count > $this->hasMany('App\Models\CartList','cart_id','id')->count()){
                        CartList::insert($reserve);
                    }
                }else{                    
                    CartList::where('cart_id', $cart_id)->delete();
                    $this->destroy($cart_id);
                }
            }                    
        }else if($total_count < $sim_count){
            CartList::where('cart_id', $cart_id)->skip($total_count)->delete();
        }
        return $this->hasMany('App\Models\CartList','cart_id','id');        
    }

    public function list1()
    {
    	$this->hasMany('App\Models\CartList','cart_id','id')
    			->where('expire_at','<', Carbon::now())->delete();
         
    	$cart_id = $this->id;		
    	$sim_count = $this->hasMany('App\Models\CartList','cart_id','id')->count();    	
        if($this->sim_count > $sim_count){
        	$limit = $this->sim_count - $sim_count;

            $box_no = Helper::get_option('sim_stock_box_no');
            $dealer = (Auth::user()->role == 5)?Auth::id():1;
        	$created_at = Carbon::now();
            $expire_at = Carbon::now()->addMinutes(20)->format('Y-m-d H:i:s');
            $query = DB::table('tbl_sim_stock')->select('id')
                                ->whereNOTIn('id', function($query){
                                     $query->select('stock_id')->from('tbl_cart_details');
                                })->inRandomOrder()->where('category', 'normal')
                                ->where('dealer_id', $dealer);
                                if($dealer == 1){
                                    $query->where('box_no', $box_no);
                                }
            $auto_selected =  $query->where('status', 1)->take($limit)->get();
            foreach($auto_selected as $selected){                                
                $reserve = ['cart_id' => $cart_id, 'stock_id' => $selected->id,
                			'created_at' => $created_at, 'expire_at' => $expire_at];
                if($this->sim_count > $this->hasMany('App\Models\CartList','cart_id','id')->count()){
                	DB::table('tbl_cart_details')->insert($reserve);  
                }
            }            
        }else if($this->sim_count < $sim_count){
        	CartList::where('cart_id', $cart_id)->skip($this->sim_count)->delete();
        }
        return $this->hasMany('App\Models\CartList','cart_id','id');        
    }

    public function selected()
    {
    	return $this->hasMany('App\Models\CartList','cart_id','id');        
    }

    public function selected_bolt()
    {        
        $sim_bolt = $app_bolt = [];
        foreach($this->selected as $selected){
            if($selected->sim_bolt){
                $sim_bolt_price = Boltons::where('id', $selected->sim_bolt)->first();
                $sim_bolt[$selected->sim_bolt][] = ['plan_name' => $sim_bolt_price->plan_name, 'price' => $sim_bolt_price->sell_price];
            }
            if($selected->app_bolt){
                $app_bolt_price = Plan::where('id', $selected->app_bolt)->first();            
                $app_bolt[$selected->app_bolt][] = ['plan_name' => $app_bolt_price->plan_name, 'price' => $app_bolt_price->sell_price];
            }
        }
        return ['sim_bolt' => $sim_bolt, 'app_bolt' => $app_bolt];       
    }

    public function user()
    {
        return $this->hasOne('App\Models\User','id','user_id');        
    }
    
    public function product()
    {
    	if ($this->category == 'plan') {
    		return $this->hasOne('App\Models\TblPlan','id','category_id');
    	} else if ($this->category == 'bundle') {
    		return $this->hasOne('App\Models\TblBundle','id','category_id');
    	}        
    }

    public function discount() {
        return $this->hasOne('App\Models\DiscountCoupon','coupon_code','discount_code');
    }
}