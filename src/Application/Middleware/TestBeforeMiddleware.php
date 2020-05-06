<?php

namespace CycleSaver\Application\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class TestBeforeMiddleware implements MiddlewareInterface
{
    /**
     * Example middleware invokable class
     *
     * @param ServerRequestInterface $request PSR-7 request
     * @param RequestHandlerInterface $handler PSR-15 request handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $existingContent = (string) $response->getBody();

        $response = new Response();
        $response->getBody()->write('BEFORE ' . $existingContent);

        return $response;
    }
}
