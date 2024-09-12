<?php

use App\Middleware\HttpKernelMiddleware;
use App\Middleware\LegacyFramework;
use App\Middleware\SlimMiddleware;
use Laminas\Stratigility\MiddlewarePipe;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $app = new MiddlewarePipe();
    $app->pipe(new HttpKernelMiddleware($context['APP_ENV'], (bool) $context['APP_DEBUG']));
    $app->pipe(new SlimMiddleware());
    $app->pipe(new LegacyFramework());
    return $app;
};
