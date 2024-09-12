<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

final class LegacyFramework implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $responseFactory = new ResponseFactory();

        ob_start();

        require_once __DIR__ . '/../../public/legacy-framework.php';

        $headers = headers_list();
        header_remove();

        $response = $responseFactory->createResponse(http_response_code());
        $response->getBody()->write(ob_get_clean());

        foreach ($headers as $header) {
            $pieces = explode(':', $header);
            $headerName = array_shift($pieces);
            $response = $response->withAddedHeader($headerName, trim(implode(':', $pieces)));
        }

        return $response;
    }
}