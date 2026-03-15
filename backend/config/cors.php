<?php

return [

    'paths'                    => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods'          => ['*'],

    'allowed_origins'          => [
        'http://localhost:5173', // Vite dev server
        'http://localhost:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:3000',
        'https://retail.nambaleconstituency.com', // frontend prod
        'https://www.retail.nambaleconstituency.com',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers'          => ['*'],

    'exposed_headers'          => [],

    'max_age'                  => 0,

    'supports_credentials'     => true, // must match withCredentials: true in Axios

];
