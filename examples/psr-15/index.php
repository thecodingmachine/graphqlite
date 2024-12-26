<?php

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiStreamEmitter;
use Laminas\Stratigility\Middleware\ErrorResponseGenerator;
use Laminas\Stratigility\MiddlewarePipe;
use Laminas\Diactoros\Server;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;

require_once __DIR__ . '/vendor/autoload.php';

$container = require 'config/container.php';

$serverRequestFactory = [ServerRequestFactory::class, 'fromGlobals'];

$errorResponseGenerator = function (Throwable $e) {
    $generator = new ErrorResponseGenerator();
    return $generator($e, new ServerRequest(), new Response());
};

$runner = new RequestHandlerRunner(
    $container->get(MiddlewarePipe::class),
    new SapiStreamEmitter(),
    $serverRequestFactory,
    $errorResponseGenerator
);
$runner->run();

