<?php

namespace CycleSaver\Application\Controllers;

use CycleSaver\Domain\Entities\User;
use CycleSaver\Domain\Repository\RepositoryException;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use CycleSaver\Test\UnitTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Slim\Http\ServerRequest;
use Slim\Psr7\Response;

class UserControllerTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @var UserRepositoryInterface|ObjectProphecy
     */
    private $repository;
    private UserController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->prophesize(UserRepositoryInterface::class);
        $this->controller = new UserController($this->repository->reveal());
    }

    public function test_createUser_should_call_user_repository_and_return_user_id()
    {
        $request = $this->prophesize(ServerRequest::class);

        $request->getParsedBody()->willReturn([
            'email' => 'test@test.com',
            'password' => 'pass',
        ]);

        $userArg = Argument::that(
            fn(User $user) => $user->getEmail() === 'test@test.com'
                && $user->getPassword() === 'pass'
        );

        $this->repository->save($userArg)->shouldBeCalled();

        $response = $this->controller->createUser($request->reveal(), new Response());

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('SUCCESS', $this->getResponseBody($response)->status);
    }

    public function test_createUser_should_return_500_if_repository_throws_error()
    {
        $request = $this->prophesize(ServerRequest::class);

        $request->getParsedBody()->willReturn([
            'email' => 'test@test.com',
            'password' => 'pass',
        ]);

        $userArg = Argument::that(
            fn(User $user) => $user->getEmail() === 'test@test.com'
                && $user->getPassword() === 'pass'
        );

        $this->repository->save($userArg)->shouldBeCalled()
            ->willThrow(new RepositoryException('error'));

        $response = $this->controller->createUser($request->reveal(), new Response());

        $expectedBody = (object) [
            'status' => 'ERROR',
            'data' => (object) [
                'message' => 'Internal error occurred when adding new user to repository'
            ]
        ];

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals($expectedBody, $this->getResponseBody($response));
    }

    public function test_createUser_should_return_422_if_body_is_missing_properties()
    {
        $request = $this->prophesize(ServerRequest::class);

        $request->getParsedBody()->willReturn([
            'password' => 'pass',
        ]);

        $response = $this->controller->createUser($request->reveal(), new Response());

        $expectedBody = (object) [
            'status' => 'ERROR',
            'data' => (object) [
                'message' => 'Email and password required to create new user.'
            ]
        ];

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals($expectedBody, $this->getResponseBody($response));
    }

    public function test_createUser_should_return_400_if_body_is_invalid_json()
    {
        $request = $this->prophesize(ServerRequest::class);

        $request->getParsedBody()->willReturn(null);

        $response = $this->controller->createUser($request->reveal(), new Response());

        $expectedBody = (object) [
            'status' => 'ERROR',
            'data' => (object) [
                'message' => 'Request body must be valid JSON.'
            ]
        ];

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals($expectedBody, $this->getResponseBody($response));
    }
}
