# Framework to framework migration

This is an example repository intended to show how to migrate from one framework to another framework. The approach is
to layer each framework on a middleware, using Zend Stratigility, Zend Diactoros and Zend HttpHandlerRunner.

**https://docs.zendframework.com/zend-httphandlerrunner/**

**https://docs.zendframework.com/zend-diactoros/**

**https://docs.zendframework.com/zend-stratigility/**

## How to run

    composer install
    php -S 127.0.0.1:8000 -t ./public
    
## Available URLs

* _/symfony_ -> Served by Symfony through `App\Middleware\SymfonyMiddleware`
* _/yii1_ -> Served by Yii 1.1 version through `App\Middleware\YiiMiddleware`
* _/yii2_ -> Served by Yii 2 version through `App\Middleware\Yii2Middleware` 
