<?php

namespace CycleSaver\Application\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class HelloController
{
    public function getHello(ServerRequestInterface $request, Response $response, $args): Response
    {
        $response->getBody()->write("Hello World Controller");
        return $response;
    }
}
