<?php

return [
    'paths' => ['api/*', 'register', 'login'],
    'allowed_origins' => ['http://localhost:3000'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
    'exposed_headers' => [],
    'max_age' => 3600,
    'supports_credentials' => true,
];