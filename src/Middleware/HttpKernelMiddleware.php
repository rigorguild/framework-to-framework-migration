<?php

namespace App\Middleware;

use App\Kernel;
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
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class HttpKernelMiddleware implements MiddlewareInterface
{
    public function __construct(
        public string $appEnv,
        public bool $appDebug
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $kernel = new Kernel($this->appEnv, $this->appDebug);
        $httpFoundationFactory = new HttpFoundationFactory();
        $psrHttpFactory = new PsrHttpFactory(new ServerRequestFactory(), new StreamFactory(), new UploadedFileFactory(), new ResponseFactory());

        $kernel->boot();
        $dispatcher = $kernel->getContainer()->get('event_dispatcher');
        $dispatcher->addListener(
            'kernel.exception',
            function (ExceptionEvent $event) use ($request, $handler, $httpFoundationFactory): void {
                if ($event->getThrowable() instanceof NotFoundHttpException) {
                    $event->allowCustomResponseCode();
                    $psr7Response = $handler->handle($request);
                    $response = $httpFoundationFactory->createResponse($psr7Response);
                    $event->setResponse($response);
                }
            }
        );

        $symfonyRequest = $httpFoundationFactory->createRequest($request);

        $symfonyResponse = $kernel->handle($symfonyRequest);
        $kernel->terminate($symfonyRequest, $symfonyResponse);

        return $psrHttpFactory->createResponse($symfonyResponse);
    }
}