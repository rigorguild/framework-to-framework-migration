<?php

use App\Kernel;
use App\Middleware\LaravelMiddleware;
use App\Middleware\SymfonyMiddleware;
use App\Middleware\LegacyFramework;
use App\Middleware\SlimMiddleware;
use Laminas\Stratigility\MiddlewarePipe;

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $app = new MiddlewarePipe();
    $app->pipe(new SymfonyMiddleware($context['APP_ENV'], (bool) $context['APP_DEBUG']));
    $app->pipe(new LaravelMiddleware());
    $app->pipe(new SlimMiddleware());
    $app->pipe(new LegacyFramework());
    return $app;
};
