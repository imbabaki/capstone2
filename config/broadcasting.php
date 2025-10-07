<?php

return [

    'default' => env('BROADCAST_DRIVER'),

    'connections' => [

        'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER','mt1'),
        'useTLS' => false,
        'host' => '192.168.100.132', // Raspberry Pi LAN IP
        'port' => 6001,
        'scheme' => env('PUSHER_SCHEME', 'http'),
        'encrypted' => false,
        'curl_options' => [
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ],
    ],
],



        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
