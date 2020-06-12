<?php

namespace CycleSaver\Application\Handlers;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpException;
use Slim\Handlers\ErrorHandler;
use Throwable;

class HttpErrorHandler extends ErrorHandler
{
    protected function respond(): ResponseInterface
    {
        $exception = $this->exception;
        $statusCode = 500;
        $description = 'An internal error has occurred while processing your request.';

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $description = $exception->getMessage();
        }

        if (
            !($exception instanceof HttpException)
            && ($exception instanceof Exception || $exception instanceof Throwable)
            && $this->displayErrorDetails
        ) {
            $description = $exception->getMessage();
        }

        $error = [
            'status' => 'ERROR',
            'data' => (object) [
                'message' => $description
            ],
        ];


        $response = $this->responseFactory->createResponse($statusCode)
            ->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($error));

        return $response;
    }
}
