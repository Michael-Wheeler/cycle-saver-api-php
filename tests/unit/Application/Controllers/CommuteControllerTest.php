<?php

namespace CycleSaver\Application\Controllers;

use CycleSaver\Domain\Repository\CommuteRepositoryInterface;
use CycleSaver\Test\UnitTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Slim\Http\ServerRequest;
use Slim\Psr7\Response;

class CommuteControllerTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @var CommuteRepositoryInterface|ObjectProphecy
     */
    private $repository;
    private CommuteController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->prophesize(CommuteRepositoryInterface::class);

        $this->controller = new CommuteController($this->repository->reveal());
    }

    public function test_getByUserId_should_return_500_error_if_repository_throws_error()
    {
        $this->repository->getCommutesByUserId(Argument::any())->shouldBeCalled()
            ->willThrow(new \InvalidArgumentException('Error when retrieving user commutes: error'));

        $request = $this->prophesize(ServerRequest::class);

        $response = $this->controller->getByUserId(
            $request->reveal(),
            new Response(),
            ['id' => 'cfe5b4e4-0407-463a-8cd2-d1a36f461984']
        );

        $this->assertResponseCodeAndMessage(500, 'Error occurred when retrieving user commutes', $response);
    }
}
