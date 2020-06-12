<?php

use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Psr7\Factory\ResponseFactory;

$app = AppFactory::create(
    new ResponseFactory(),
    $container
);

$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();

$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

require __DIR__ . '/Handlers/ErrorHandler.php';
require __DIR__ . '/Middleware/Middleware.php';
require __DIR__ . '/RouteMapper.php';

return $app;
