<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use yii\web\NotFoundHttpException;
use Zend\Diactoros\ResponseFactory;

final class Yii2Middleware implements MiddlewareInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var boolean
     */
    private $debug;

    /**
     * @var string
     */
    private $env;

    public function __construct(string $env, bool $debug, ResponseFactoryInterface $responseFactory)
    {
        $this->env = $env;
        $this->debug = $debug;
        $this->responseFactory = $responseFactory;
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
        // comment out the following two lines when deployed to production
        defined('YII_DEBUG') or define('YII_DEBUG', $this->debug);
        defined('YII_ENV') or define('YII_ENV', $this->env);

        require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
        $config = require __DIR__ . '/../../config/web.php';

        \ob_start();
        
        try {
            (new \yii\web\Application($config))->run();
        } catch (NotFoundHttpException $nfhe) {
            return $handler->handle($request);
        }

        $headers = \headers_list();
        \header_remove();

        $response = $this->responseFactory->createResponse(\http_response_code());
        $response->getBody()->write(\ob_get_clean());

        foreach ($headers as $header) {
            $pieces = \explode(':', $header);
            $headerName = \array_shift($pieces);
            $response = $response->withAddedHeader($headerName, \trim(\implode(':', $pieces)));
        }

        return $response;
    }
}
