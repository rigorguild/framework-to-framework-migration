# Framework to framework migration

This is an example repository intended to show how to migrate from one framework to another framework. The approach is
to layer each framework on a middleware, using Zend Stratigility, Zend Diactoros and Zend HttpHandlerRunner.

* **[Zend Stratigility](https://docs.zendframework.com/zend-stratigility/)** in order to build the middlewares pipe.
* **[Zend HttpHandlerRunner](https://docs.zendframework.com/zend-httphandlerrunner/)** in order to run the middleware pipe.
* **[Zend Diactoros](https://docs.zendframework.com/zend-diactoros/)** as PSR-7 implementation.

## How to run

    composer install
    php -S 127.0.0.1:8000 -t ./public
    
## Available URLs

* _/symfony_ -> Served by Symfony through `App\Middleware\SymfonyMiddleware`
* _/yii1_ -> Served by Yii 1.1 version through `App\Middleware\YiiMiddleware`
* _/yii2_ -> Served by Yii 2 version through `App\Middleware\Yii2Middleware` 
* _/silex_ -> Served by Silex through `App\Middleware\SilexMiddleware` 
  * _/silex/images/image.jpg_ 
