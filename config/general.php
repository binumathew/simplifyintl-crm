<?php

return [

    /*
    |--------------------------------------------------------------------------
    | General Settings
    |--------------------------------------------------------------------------
    |
    | 
    |
    */

    'settings' => [
        'sms_alert_email' => env('SMS_ALERT_MAIL','info@geokall.com'),
        'support_email'=>'info@geokall.com',
        'technical_support'=>env('SUPPORT_EMAIL','arun@gencomtel.com'),
        'bcc_emails'=> [
                [
                    'email' => 'sojan@gencomtel.com',
                    'name' => 'Sojan'
                ],
                [
                    'email' => 'arun.raj610@gmail.com',
                    'name' => 'Arun'
                ]
            
            ],
        'bill_limit'=>20.00,
        'instant_failed_retry_days' => 1,
        'stripe_failed_fee'=>10.00,
        'bars_VUK'=>['OUT_CALLS','GPRS'],
        'bars_O2'=>['OUT_CALLS_DATA'],
    ],

];
