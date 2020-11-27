<?php
namespace App\Helpers;


use Carbon\Carbon;
use GuzzleHttp\Client;
use Cache;
use Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use Hashids\Hashids;
use Propaganistas\LaravelPhone\PhoneNumber;
use Twilio\Rest\Client as TwilioClient;
use Twilio\TwiML\VoiceResponse;

use App\Models\Country;
use App\Models\Options;
use chillerlan\QRCode\{QRCode, QROptions};

class Utils {

    public static function encodeHash($op){
        $hashids = new Hashids('IGD1XqkdzXp4JqPS', 20);
        $hashids->_lower_max_int_value = PHP_INT_MAX;
        return $hashids->encode($op);
    }

    public static function decodeHash($hash){
        $hashids = new Hashids('IGD1XqkdzXp4JqPS', 20);
        return $hashids->decode($hash);
    }

    public static function otp($length = 4){
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            if ($i == 0) {
                $result .= mt_rand(1, 9);
            } else {
                $result .= mt_rand(0, 9);
            }
        }
        return intval($result);
    }


    public static function parsePhoneNo($phone){
            try{
                return PhoneNumber::make($phone, 'AUTO');
            }catch(\Exception $e){
                return false;
            }
    }

    public static function countries(){
        try{
            return Cache::remember('countries',300, function () {
                return Country::where('status', '1')->get();
            });
        }catch(\Exception $e){
            return false;
        }
    }

    public static function country($id){
        try{
            return Cache::remember('countries:'.$id,300, function () use ($id) {
                return Country::where('id', $id)->where('status', '1')->first();
            });
        }catch(\Exception $e){
            return false;
        }
    }

    public static function countryISO2($iso){
        try{
            return Cache::remember('countries_iso2:'.$iso,300, function () use ($iso) {
                return Country::where('short_code', $iso)->where('status', '1')->first();
            });
        }catch(\Exception $e){
            return false;
        }
    }

    public static function settings($key){
        try{
            $settings =  Cache::remember('settings',86400, function () {
                return Options::all();
            });
            return $settings->where('name',$key)->values()->first()->toArray()['value'];

        }catch(\Exception $e){
            return false;
        }
    }

    public static function sms($phone,$body) {
        try{
            $sid = self::settings('twilio_account_sid');
            $token = self::settings('twilio_auth_token');
            $sender = self::settings('twilio_number');
            $client = new TwilioClient($sid, $token);
            $response = $client->messages->create(
                $phone,  [
                'from' => $sender,
                'body' => $body
               ]
            );
            return $response;
        }
        catch(RestException $e){
            if ($e->getCode() === 21211) {
                return 'invalid-number';
            }
            return false;
        }
        catch(\Exception $e){
            return false;
        }
    }

    public static function otpCall($phone,$otp) {
        try{
            $sid = self::settings('twilio_account_sid');
            $token = self::settings('twilio_auth_token');
            $sender = self::settings('twilio_number');
            $client = new TwilioClient($sid, $token);
            $otpHash = self::encodeHash($otp);
            $response = $client->account->calls->create( $phone,$sender,[
                'url' => config('app.url').'/api-v1/twilio-ml/'.$otpHash
            ]);
            return $response;
        }
        catch(RestException $e){
            if ($e->getCode() === 21211) {
                return 'invalid-number';
            }
            return false;
        }
        catch(\Exception $e){
            return false;
        }
    }


    public static function saveFile($path,$file_content,$disk = 'public'){
        try{
            if(!Storage::disk($disk)->put($path, $file_content)) {
                return false;
            }
            return true;
        }catch(\Exception $e){
            return false;
        }
    }

    public static function paginate($result){
        return [
            'current_page' => $result->currentPage(),
            'data' => $result->items(),
            'from' => $result->firstItem(),
            'last_page' => $result->lastPage(),
            'per_page' => $result->perPage(),
            'to' => $result->lastItem(),
            'total' => $result->total(),
        ];
    }

    public static function storeFile($path,$fileContents,$disk = 'gcs'){

        try{
            $disk = Storage::disk($disk);
            $disk->put($path, $fileContents);
            return true;
        }catch(\Exception $e){
            return false;
        }
    }

    public static function qrcode($txt){

        try{
            $options = new QROptions([
                'version'      => 7,
                'outputType'   => QRCode::OUTPUT_MARKUP_SVG,
                'eccLevel'     => QRCode::ECC_L,
                'svgViewBoxSize' => 530,
                'addQuietzone' => true,
                'cssClass'     => 'my-css-class',
                'svgOpacity'   => 1.0,
                'svgDefs'      => '
                    <linearGradient id="g2">
                        <stop offset="0%" stop-color="#39F" />
                        <stop offset="100%" stop-color="#F3F" />
                    </linearGradient>
                    <linearGradient id="g1">
                        <stop offset="0%" stop-color="#F3F" />
                        <stop offset="100%" stop-color="#39F" />
                    </linearGradient>
                    <style>rect{shape-rendering:crispEdges}</style>',
                'moduleValues' => [
                    // finder
                    1536 => 'url(#g1)', // dark (true)
                    6    => '#fff', // light (false)
                    // alignment
                    2560 => 'url(#g1)',
                    10   => '#fff',
                    // timing
                    3072 => 'url(#g1)',
                    12   => '#fff',
                    // format
                    3584 => 'url(#g1)',
                    14   => '#fff',
                    // version
                    4096 => 'url(#g1)',
                    16   => '#fff',
                    // data
                    1024 => 'url(#g2)',
                    4    => '#fff',
                    // darkmodule
                    512  => 'url(#g1)',
                    // separator
                    8    => '#fff',
                    // quietzone
                    18   => '#fff',
                ],
            ]);
            $qrcode = (new QRCode($options))->render($txt);
            return $qrcode;
        }catch(\Exception $e){
            return false;
        }
    }
}
