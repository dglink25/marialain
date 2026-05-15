<?php

return [

 
    'gateway_url' => env('WHATSAPP_GATEWAY_URL', 'https://service-whatsapp-careasy.vercel.app'),

 
    'send_path' => env('WHATSAPP_GATEWAY_SEND_PATH', '/send'),

    /*
    |--------------------------------------------------------------------------
    | Clé secrète API
    |--------------------------------------------------------------------------
    */
    'api_secret' => env('WHATSAPP_API_SECRET', 'clé secrète'),


    'enabled' => env('WHATSAPP_ENABLED', true),

 
    'sender' => env('WHATSAPP_SENDER', '+22994119476'),

];