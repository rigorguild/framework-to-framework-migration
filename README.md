# Framework to framework migration

This is an example repository intended to show how to migrate from one framework to another using [PSR7](https://www.php-fig.org/psr/psr-7/), [PSR15](https://www.php-fig.org/psr/psr-15/) and [PSR17](https://www.php-fig.org/psr/psr-17/). The approach is
to layer each framework on a middleware, using Symfony Runtime component and Laminas Stratigility's Middleware pipeline feature.

* **[Symfony Runtime component](https://symfony.com/doc/current/components/runtime.html)**
* **[PSR17 runtime](https://github.com/php-runtime/psr-17)**
* **[Laminas Stratigility's middleware pipeline to compose different middlewares](https://docs.laminas.dev/laminas-stratigility/v4/executing-middleware/)**.

Using the PSR17 runtime we are able to run any PSR17 complaint HTTP Server Request Handler just by returning it in the `public/index.php` main function 👉 The class `Laminas\Stratigility\MiddlewarePipe` is an implementation of that interface, so we can just instanciate it and register all the middlewares that should be an implementation of the `Psr\Http\Server\MiddlewareInterface` from the PSR15 standard. You will find all the implementations in the namespace [`App\Middleware`](src/Middleware).

## How to run

Install the Symfony CLI first. Instructions 👉 **[here](https://symfony.com/download#step-1-install-symfony-cli)** 👈. Then 👇

    php composer.phar install
    symfony serve -d

## Available URLs

* _/symfony_ -> Served by Symfony version 7.
* _/slim_ -> Served by Slim version 4 👉 [`App\Middleware\SlimMiddleware`](src/Middleware/SlimMiddleware.php)
* Any URL that does not match any of these will return a 404 from Slim which is the last level in the middleware stack.