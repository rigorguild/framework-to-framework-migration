<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Silex\AppServiceProvider;
use Silex\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

final class SilexMiddleware implements MiddlewareInterface
{
    /**
     * @var bool
     */
    private $debug = false;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $httpFoundationFactory = new HttpFoundationFactory();
        $psr7Factory = new DiactorosFactory();
        
        $app = new Application();
        $app->register(new AppServiceProvider());
        
        if ($this->debug) {
            $app['debug'] = true;
        }
        
        $app->error(function(\Exception $e, Request $httpFoundationRequest, int $code, GetResponseForExceptionEvent $event) use ($handler, $httpFoundationFactory, $request) {
            if (404 === $code) {
                $event->allowCustomResponseCode();
                $psr7Response = $handler->handle($request);
                return $httpFoundationFactory->createResponse($psr7Response);
            }
        });

        $httpFoundationRequest = $httpFoundationFactory->createRequest($request);
        $httpFoundationResponse = $app->handle($httpFoundationRequest);
        $app->terminate($httpFoundationRequest, $httpFoundationResponse);
        
        return $psr7Factory->createResponse($httpFoundationResponse);
    }
}
