<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;

final class SlimMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $app = AppFactory::create();

        $app->get('/slim', function (ServerRequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write("Hello world from Slim!");
            return $response;
        });

        return $app->handle($request);
    }
}