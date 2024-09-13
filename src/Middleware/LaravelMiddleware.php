<?php

namespace App\Middleware;

use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\UploadedFileFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

final class LaravelMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $httpFoundationFactory = new HttpFoundationFactory();
        $psrHttpFactory = new PsrHttpFactory(new ServerRequestFactory(), new StreamFactory(), new UploadedFileFactory(), new ResponseFactory());

        $app = (require_once __DIR__.'/../../bootstrap/app.php');
        assert($app instanceof Application);
        $kernel = $app->make(HttpKernelContract::class);
        assert($kernel instanceof HttpKernelContract);

        $app['router']->fallback(
            static fn() => $httpFoundationFactory->createResponse($handler->handle($request))
        );

        $laravelRequest = Request::createFromBase($httpFoundationFactory->createRequest($request));
        $response = $kernel->handle($laravelRequest);
        $kernel->terminate($laravelRequest, $response);

        return $psrHttpFactory->createResponse($response);
    }
}
