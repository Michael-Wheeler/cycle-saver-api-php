<?php

namespace CycleSaver\Application\Controllers;

use CycleSaver\Application\ResponseFactory;
use CycleSaver\Domain\Entities\User;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use Exception;
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

    public function createUser(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $body = $request->getParsedBody();

        if ($body === null) {
            return ResponseFactory::createBadRequestResponse('Request body must be valid JSON.', $response);
        }

        if (!isset($body['email']) || !isset($body['password'])) {
            return ResponseFactory::createBadRequestResponse(
                'Create user request must contain email and password.',
                $response
            );
        }

        $user = (new User(Uuid::uuid4()))
            ->setEmail($body['email'])
            ->setPassword($body['password']);

        try {
            $id = $this->repository->save($user);
        } catch (Exception $e) {
            return ResponseFactory::createInternalErrorResponse($e->getMessage(), $response);
        }

        return ResponseFactory::createSuccessfulCreationResponse(
            (object) ['id' => (string) $id],
            $response
        );
    }
}
