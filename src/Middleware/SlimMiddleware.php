<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Throwable;

final class SlimMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        $errorMiddleware = $app->addErrorMiddleware(true, true, true);
        $errorMiddleware->setErrorHandler(
            HttpNotFoundException::class,
            fn(ServerRequestInterface $request) => $handler->handle($request)
        );

        $app->get('/slim', function (ServerRequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write("Hello world from Slim!");
            return $response;
        });

        return $app->handle($request);
    }
}