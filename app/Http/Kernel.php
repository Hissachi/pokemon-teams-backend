<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        // Other middleware...
        \Fruitcake\Cors\HandleCors::class, // Add this line for CORS
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // Other middleware...
        ],

        'api' => [
            // Other middleware...
            \Fruitcake\Cors\HandleCors::class, // Add this line for API routes
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // Other middleware...
    ];
}