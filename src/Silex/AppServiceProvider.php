<?php

declare(strict_types=1);

namespace App\Silex;

use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use League\Glide;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AppServiceProvider implements ServiceProviderInterface, ControllerProviderInterface, BootableProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $app A container instance
     */
    public function register(Container $app)
    {
        $app['glide.source'] = __DIR__ . '/../../public/images';
        
        $app[Glide\Server::class] = function (Container $app) {
            return Glide\ServerFactory::create([
                'source' => $app['glide.source'],
                'cache' => new Filesystem(new MemoryAdapter()), 
                'response' => new Glide\Responses\SymfonyResponseFactory()
            ]);
        };
    }

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];
        
        $controllers->get('/silex', function(): Response {
            return new Response('<html><body><h1>Hello from Silex!!</h1></body></html>');
        });
        
        $controllers->get('/silex/images/{image}', function(string $image, Request $request, Application $app): Response {
            return $app[Glide\Server::class]->getImageResponse($image, $request->query->all());
        });
        
        return $controllers;
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     *
     * @param Application $app
     */
    public function boot(Application $app)
    {
        $app->mount('/', $this);
    }
}
