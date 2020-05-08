<?php

namespace CycleSaver\Application\Controllers;

use CycleSaver\Application\ResponseFactory;
use CycleSaver\Domain\Entities\User;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use CycleSaver\Infrastructure\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class UserController
{
    private UserRepositoryInterface $repository;

//    public function __construct(UserRepositoryInterface $repository)
//    {
//        $this->repository = $repository;
//    }

    public function createUser(ServerRequestInterface $request, Response $response, $args): ResponseInterface
    {
        $this->repository = new UserRepository();
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

        $user = (new User())
            ->setEmail($body['email'])
            ->setPassword($body['password']);

        $this->repository->save($user);

        return ResponseFactory::createSuccessfulCreationResponse($user->toObject(), $response);
    }
}
