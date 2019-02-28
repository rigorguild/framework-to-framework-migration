<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class YiiMiddleware implements MiddlewareInterface
{
    /**
     * @var boolean
     */
    private $debug;

    /**
     * @var int 
     */
    private $yiiTraceLevel;
    
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(bool $debug, ResponseFactoryInterface $responseFactory, ?int $yiiTraceLevel = 3)
    {
        $this->debug = $debug;
        $this->yiiTraceLevel = $yiiTraceLevel;
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
        $yii = __DIR__ .'/../../framework/yii.php';
        $config = __DIR__ .'/../../protected/config/main.php';

        // remove the following lines when in production mode
        defined('YII_DEBUG') or define('YII_DEBUG', $this->debug);
        // specify how many levels of call stack should be shown in each log message
        defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', $this->yiiTraceLevel);

        \ob_start();

        require_once $yii;
        \OldYii::createWebApplication($config)->run();

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
