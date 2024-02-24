<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*','register','sanctum/csrf-cookie','login','reset-password','forgot-password'],

    'allowed_methods' => ['POST', 'GET', 'OPTIONS', 'PUT', 'DELETE'],

    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000','https://quizma-front-end.onrender.com')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Origin', 'Authorization', 'Accept', 'Client-Security-Token', 'Accept-Encoding', 'X-Auth-Token', 'X-CSRF-Token'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
