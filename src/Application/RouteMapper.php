<?php

use CycleSaver\Application\Controllers\HelloController;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

$app->get('/', function (ServerRequestInterface $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

//TODO Use single controller file for all endpoint methods?
$app->get('/hello', HelloController::class . ':getHello');

$app->get('/hello/{name}', function (ServerRequestInterface $request, Response $response, $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});
