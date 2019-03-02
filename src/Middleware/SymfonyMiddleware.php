<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Kernel;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Psr\Http\Message\ResponseInterface;

final class SymfonyMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $appEnv;

    /**
     * @var bool
     */
    private $appDebug;

    /**
     * @var bool
     */
    private $booted = false;

    public function __construct(string $appEnv, bool $appDebug)
    {
        $this->appEnv = $appEnv;
        $this->appDebug = $appDebug;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $httpFoundationFactory = new HttpFoundationFactory();
        $psr7Factory = new DiactorosFactory();

        $kernel = new Kernel($this->appEnv, $this->appDebug);
        $kernel->boot();
        $dispatcher = $kernel->getContainer()->get('event_dispatcher');
        $dispatcher->addListener(
            'kernel.exception',
            function (GetResponseForExceptionEvent $event) use ($request, $handler, $httpFoundationFactory): void {
                if ($event->getException() instanceof NotFoundHttpException) {
                    $event->allowCustomResponseCode();
                    $psr7Response = $handler->handle($request);
                    $response = $httpFoundationFactory->createResponse($psr7Response);
                    $event->setResponse($response);
                }
            }
        );

        $httpFoundationRequest = $httpFoundationFactory->createRequest($request);
        
        $httpFoundationResponse = $kernel->handle($httpFoundationRequest);
        $kernel->terminate($httpFoundationRequest, $httpFoundationResponse);

        return $psr7Factory->createResponse($httpFoundationResponse);
    }
}
