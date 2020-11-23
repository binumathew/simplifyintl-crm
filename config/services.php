<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],


     /*
    |--------------------------------------------------------------------------
    | Stripe
    |--------------------------------------------------------------------------
    */


    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],


     /*
    |--------------------------------------------------------------------------
    | Gocardless
    |--------------------------------------------------------------------------
    */

    'gocardless' => [
        'environment' => env('GOCARDLESS_ENVIRONMENT', 'sandbox'),
        'token' => env('GC_ACCESS_TOKEN'),
        'webhook_secret'    =>  env('GOCARDLESS_WEBHOOK_ENDPOINT_SECRET'),
    ],


    /*
    |--------------------------------------------------------------------------
    | Braintree Config Key
    |--------------------------------------------------------------------------
    */
    'braintree' => [
        'environment' => env('BTREE_ENVIRONMENT'),
        'merchantId' => env('BTREE_MERCHANT_ID'),
        'publicKey' => env('BTREE_PUBLIC_KEY'),
        'privateKey' => env('BTREE_PRIVATE_KEY'),
    ],
    /*
    |--------------------------------------------------------------------------
    | Braintree Config Key
    |--------------------------------------------------------------------------
    */
    'globalsim' =>  [
        'api'   =>  env('GLOBAL_SIM_API'),
        'username'  =>  env('GLOBAL_SIM_USERNAME'),
        'password'  =>  env('GLOBAL_SIM_PASSWORD'),
    ]

];
