<?php

use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Factory\ServerRequestCreatorFactory;

require __DIR__ . '/../../vendor/autoload.php';

//// Start PHP session
//session_start();

//// Create Container
//$container = new Container();
//
//AppFactory::setContainer($container);

$app = AppFactory::create(new ResponseFactory());

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
