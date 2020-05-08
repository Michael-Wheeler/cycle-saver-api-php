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

/**
 * Add Error Handling
 */
require __DIR__ . '/Handlers/ErrorHandler.php';

/**
 * Add Middleware
 */
require __DIR__ . '/Middleware/Middleware.php';

/**
 * Define App Routes
 */
require __DIR__ . '/RouteMapper.php';
