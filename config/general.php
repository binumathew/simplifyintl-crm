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
        'sms_alert_email' => env('SMS_ALERT_MAIL','hello@nexmobile.co.uk'),
        'support_email'=>'usman.azhar@gencomtel.com',
        'bcc_emails'=> [
                [
                    'email' => 'sojan@gencomtel.com',
                    'name' => 'Sojan'
                ],
                [
                    'email' => 'usman.azhar@gencomtel.com',
                    'name' => 'Usman'
                ],
                [
                    'email' => 'jijo.joseph@gencomtel.com',
                    'name' => 'Jijo'
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
