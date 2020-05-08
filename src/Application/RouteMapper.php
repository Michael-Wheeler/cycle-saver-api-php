<?php

use CycleSaver\Application\Controllers\HelloController;
use CycleSaver\Application\Controllers\UserController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

//TODO Use single controller file for all endpoint methods?
//TODO could use groups to break route mapping file up if needed
$app->get('/hello', HelloController::class . ':getHello');
$app->get('/hello/{name}', HelloController::class . ':getHelloName');

$app->post('/user', UserController::class . ':createUser');
