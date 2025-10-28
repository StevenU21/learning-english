<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register'],

    'allowed_methods' => ['*'],

    // Include the origins you use in development (exact origin strings)
    'allowed_origins' => [
        'http://localhost:8081',   // Expo web packager (or your dev origin)
        'http://localhost:19006',  // Expo web on other ports you use
        'http://localhost:19000',
        'https://nativo.domcloud.dev', // your production origin if needed
        'http://leaning-english.com', // your local dev origin if needed
    ],

    // If you want to accept subdomains or wildcard hosts, use allowed_origins_patterns
    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // IMPORTANT: allow credentials when using cookies / Sanctum
    'supports_credentials' => true,
];