<?php

namespace CycleSaver\Application\Controllers;

use CycleSaver\Domain\Repository\RepositoryException;
use CycleSaver\Domain\Services\StravaService;
use CycleSaver\UnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\Uuid;
use Slim\Http\ServerRequest;
use Slim\Psr7\Response;

class StravaControllerTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @var StravaService|ObjectProphecy
     */
    private $stravaService;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stravaService = $this->prophesize(StravaService::class);
        $this->controller = new StravaController($this->stravaService->reveal());
    }

    public function test_newUser_should_pass_auth_code_to_service_and_return_user_id()
    {
        $request = $this->prophesize(ServerRequest::class);

        $request->getQueryParams()->willReturn(['code' => 'auth_code']);

        $this->stravaService->createStravaUser('auth_code')->shouldBeCalled()
            ->willReturn(Uuid::fromString('2db20b91-6b40-4642-a40b-80ce6db0cad2'));

        $response = $this->controller->newUser($request->reveal(), new Response());

        $expectedBody = (object) [
            'status' => 'SUCCESS',
            'data' => (object) [
                'id' => '2db20b91-6b40-4642-a40b-80ce6db0cad2'
            ]
        ];

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($expectedBody, $this->getResponseBody($response));
    }

    public function test_newUser_should_return_500_if_user_setup_fails()
    {
        $request = $this->prophesize(ServerRequest::class);

        $request->getQueryParams()->willReturn(['code' => 'auth_code']);

        $this->stravaService->createStravaUser('auth_code')->shouldBeCalled()
            ->willThrow(new RepositoryException('error'));

        $response = $this->controller->newUser($request->reveal(), new Response());

        $expectedBody = (object) [
            'status' => 'ERROR',
            'data' => (object) [
                'message' => 'Internal error occurred when creating new user from Strava data.'
            ]
        ];

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals($expectedBody, $this->getResponseBody($response));
    }

    public function test_newUser_should_return_400_if_auth_code_missing()
    {
        $request = $this->prophesize(ServerRequest::class);

        $request->getQueryParams()->willReturn([]);

        $this->stravaService->createStravaUser()->shouldNotBeCalled();

        $response = $this->controller->newUser($request->reveal(), new Response());

        $expectedBody = (object) [
            'status' => 'ERROR',
            'data' => (object) [
                'message' => 'Strava auth code required to connect to Strava.'
            ]
        ];

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals($expectedBody, $this->getResponseBody($response));
    }
}
