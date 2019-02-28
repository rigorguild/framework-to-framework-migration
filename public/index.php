<?php

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Stratigility\Middleware\ErrorResponseGenerator;

require dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$responseFactory = new ResponseFactory();

$pipe = new Zend\Stratigility\MiddlewarePipe();
$pipe->pipe(new App\Middleware\SymfonyMiddleware($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']));
$pipe->pipe(new App\Middleware\Yii2Middleware($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG'], $responseFactory));
$pipe->pipe(new App\Middleware\YiiMiddleware((bool)$_SERVER['APP_DEBUG'], $responseFactory));

$emitterStack = new Zend\HttpHandlerRunner\Emitter\EmitterStack();
$emitterStack->push(new Zend\HttpHandlerRunner\Emitter\SapiEmitter());

$runner = new Zend\HttpHandlerRunner\RequestHandlerRunner(
    $pipe,
    $emitterStack,
    function (): ServerRequestInterface {
        return ServerRequestFactory::fromGlobals();
    },
    function (Throwable $e) {
        $generator = new ErrorResponseGenerator((bool)$_SERVER['APP_DEBUG']);
        return $generator($e, ServerRequestFactory::fromGlobals(), (new ResponseFactory())->createResponse());
    }
);

$runner->run();
