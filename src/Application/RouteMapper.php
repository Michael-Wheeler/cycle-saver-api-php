<?php

use CycleSaver\Application\Controllers\HelloController;
use CycleSaver\Application\Controllers\StravaController;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

$app->get('/', function (ServerRequestInterface $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

//TODO Use single controller file for all endpoint methods?
//TODO could use groups to break route mapping file up if needed
$app->get('/hello', HelloController::class . ':getHello');
$app->get('/hello/{name}', HelloController::class . ':getHelloName');

$app->post('/strava/authorise', StravaController::class . ':authorise');
