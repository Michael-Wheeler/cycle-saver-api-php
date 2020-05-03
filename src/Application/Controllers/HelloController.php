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

    public function getHelloName(ServerRequestInterface $request, Response $response, $args): Response
    {
        $name = isset($args['name']) ? $args['name'] : null;

        if (!$name) {
            throw new \InvalidArgumentException('Need a name m8');
        }

        $response->getBody()->write("Hello, $name");
        return $response;
    }
}
