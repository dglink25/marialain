<?php
// config/cloudinary.php

return [
    'cloud_url' => env('CLOUDINARY_URL'),
    
    'cloud' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME', 'marialain'),
        'api_key' => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
    ],
    
    'url' => [
        'secure' => env('CLOUDINARY_SECURE', true),
    ],
    
    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),
    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),
    'upload_route' => env('CLOUDINARY_UPLOAD_ROUTE'),
    'upload_action' => env('CLOUDINARY_UPLOAD_ACTION'),
];