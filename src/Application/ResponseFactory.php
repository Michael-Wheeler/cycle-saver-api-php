<?php

namespace CycleSaver\Application;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseFactory
{
    public static function createBadRequestResponse(string $message, ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write(json_encode([
            'status' => 'FAILED',
            'data' => [
                'message' => $message
            ]
        ]));

        return $response
            ->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST)
            ->withAddedHeader('Content-Type', 'application/json');
    }

    public static function createEmptySuccessResponse(string $message, ResponseInterface $response): ResponseInterface
    {
        return $response->withStatus(StatusCodeInterface::STATUS_CREATED);
    }

    public static function createSuccessfulCreationResponse(object $data, ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write(json_encode([
            'status' => 'SUCCESS',
            'data' => $data
        ]));

        return $response
            ->withStatus(StatusCodeInterface::STATUS_CREATED)
            ->withAddedHeader('Content-Type', 'application/json');
    }

    public static function createSuccessResponse(object $data, ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write(json_encode([
            'status' => 'SUCCESS',
            'data' => $data
        ]));

        return $response
            ->withStatus(StatusCodeInterface::STATUS_OK)
            ->withAddedHeader('Content-Type', 'application/json');
    }
}
