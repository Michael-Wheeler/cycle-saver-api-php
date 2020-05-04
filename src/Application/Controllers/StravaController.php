<?php

namespace CycleSaver\Application\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class StravaController
{
    public function authorise(ServerRequestInterface $request, Response $response, $args): Response
    {
        $response->getBody()->write("Hello World Controller");
        return $response;
    }
}
