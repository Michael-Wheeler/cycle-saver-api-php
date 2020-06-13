<?php

namespace CycleSaver\Application\Controllers;

use CycleSaver\Application\ResponseFactory;
use CycleSaver\Domain\Entities\User;
use CycleSaver\Domain\Repository\RepositoryException;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class UserController
{
    private UserRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function createUser(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $request->getParsedBody();

        if ($body === null) {
            return ResponseFactory::createBadRequestResponse('Request body must be valid JSON.', $response);
        }

        if (!isset($body['email']) || !isset($body['password'])) {
            return ResponseFactory::createUnprocessableEntityResponse(
                'Email and password required to create new user.',
                $response
            );
        }

        $user = (new User(Uuid::uuid4()))
            ->setEmail($body['email'])
            ->setPassword($body['password']);

        try {
            $id = $this->repository->save($user);
        } catch (RepositoryException $e) {
            return ResponseFactory::createInternalErrorResponse(
                'Internal error occurred when adding new user to repository',
                $response
            );
        }

        return ResponseFactory::createSuccessfulCreationResponse(
            (object) ['id' => (string) $id],
            $response
        );
    }
}
