<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

"api" => [
    'headers' => [
        'api_key'=>env('TELNA_API_KEY'),
        'login_id'=>env('TELNA_LOGIN_ID'),
        'access_token'=>env('TELNA_ACCESS_TOKEN'),
        'content_type'=>'application/json',
    ],
    'base_url'=>env('TELNA_BASE_URL'),
    'base_path'=>env('TELNA_BASE_PATH'),
    'distributor_id'=>env('TELNA_DISTRIBUTOR_ID')
],
"short_code"=>env('TELNA_SHORT_CODE')


];