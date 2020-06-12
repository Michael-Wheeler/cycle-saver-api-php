<?php

namespace CycleSaver\Infrastructure\Strava\Client;

use CycleSaver\Infrastructure\Strava\Exception\StravaAuthClientException;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class StravaApiAuthClientTest extends TestCase
{
    use ProphecyTrait;

    private StravaApiAuthClient $authClient;
    /**
     * @var StravaContext|ObjectProphecy
     */
    private $context;
    /**
     * @var ClientInterface|ObjectProphecy
     */
    private $httpClient;
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->context = $this->prophesize(StravaContext::class);
        $this->httpClient = $this->prophesize(ClientInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->authClient = new StravaApiAuthClient(
            $this->context->reveal(),
            $this->httpClient->reveal(),
            $this->logger->reveal()
        );
    }

    public function test_authoriseUser_should_call_strava_and_parse_tokens_and_id()
    {
        $this->stravaApiContext();

        $this->httpClient->request(
            'POST',
            'https://www.strava.com/api/v3/oauth/token',
            [
                'query' => [
                    'client_id' => 'test',
                    'client_secret' => '886e80d1-4f64-46ad-a509-f2f682665dbf',
                    'code' => '63390f47-73a1-47c0-8fbb-f3fa258e62c8',
                    'grant_type' => 'authorization_code',
                ]
            ]
        )->shouldBeCalled()->willReturn(
            new Response(
                200,
                ['Content-Type' => 'application/json; charset=utf-8'],
                $this->authResponseBody()
            )
        );

        [$userId, $refreshToken] = $this->authClient->authenticateUser('63390f47-73a1-47c0-8fbb-f3fa258e62c8');

        $this->assertEquals('05e6c43c-7751-41f5-be29-7fc6d254f677', $refreshToken);
        $this->assertEquals('1118050', $userId);
    }

    public function test_authoriseUser_should_throw_Strava_auth_error_if_api_call_fails()
    {
        $this->stravaApiContext();

        $this->httpClient->request(Argument::any(), Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willThrow(new Exception('error message'));

        $this->logger->error('Strava auth client error when calling Strava auth code API: error message')
            ->shouldBeCalled();

        $this->expectException(StravaAuthClientException::class);
        $this->expectExceptionMessage('Strava auth client error when calling Strava auth code API: error message');
        $this->authClient->authenticateUser('63390f47-73a1-47c0-8fbb-f3fa258e62c8');
    }

    public function test_authoriseUser_should_throw_Strava_auth_client_error_if_api_returns_invalid_json()
    {
        $this->stravaApiContext();

        $this->httpClient->request(Argument::any(), Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json; charset=utf-8'],
                    'invalid'
                )
            );

        $this->logger->error('Strava auth client error: Unable to parse JSON response body')
            ->shouldBeCalled();

        $this->expectException(StravaAuthClientException::class);
        $this->expectExceptionMessage('Strava auth client error: Unable to parse JSON response body');
        $this->authClient->authenticateUser('63390f47-73a1-47c0-8fbb-f3fa258e62c8');
    }

    public function test_authoriseUser_should_throw_Strava_auth_client_error_if_token_missing_from_response()
    {
        $this->stravaApiContext();

        $this->httpClient->request(Argument::any(), Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json; charset=utf-8'],
                    json_encode((object) [
                        "refresh_token" => "a5bb054e-5205-41fe-be77-8f6e45e1e4d5",
                    ])
                )
            );

        $this->logger->error('Strava auth client error: Response does not contain refresh token or athlete ID')
            ->shouldBeCalled();

        $this->expectException(StravaAuthClientException::class);
        $this->expectExceptionMessage(
            'Strava auth client error: Response does not contain refresh token or athlete ID'
        );

        $this->authClient->authenticateUser('63390f47-73a1-47c0-8fbb-f3fa258e62c8');
    }

    public function test_getAccessToken_should_call_strava_with_refresh_token_and_update_user()
    {
        $this->stravaApiContext();

        $this->httpClient->request(
            'POST',
            'https://www.strava.com/api/v3/oauth/token',
            [
                'query' => [
                    'client_id' => 'test',
                    'client_secret' => '886e80d1-4f64-46ad-a509-f2f682665dbf',
                    'grant_type' => 'refresh_token',
                    'refresh_token' => 'oldToken'
                ]
            ]
        )->shouldBeCalled()->willReturn(
            new Response(
                200,
                ['Content-Type' => 'application/json; charset=utf-8'],
                json_encode((object) [
                    "token_type" => "Bearer",
                    "expires_at" => 1589153055,
                    "expires_in" => 21600,
                    "refresh_token" => "refreshToken",
                    "access_token" => "accessToken",
                ])
            )
        );

        [$accessToken, $refreshToken] = $this->authClient->getAccessToken('oldToken');

        $this->assertEquals('accessToken', $accessToken);
        $this->assertEquals('refreshToken', $refreshToken);
    }

    public function test_getAccessToken_should_throw_Strava_auth_error_if_api_call_fails()
    {
        $this->stravaApiContext();

        $this->httpClient->request(Argument::any(), Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willThrow(new Exception('error message'));

        $this->logger->error('Strava auth client error when calling Strava refresh token API: error message')
            ->shouldBeCalled();

        $this->expectException(StravaAuthClientException::class);
        $this->expectExceptionMessage('Strava auth client error when calling Strava refresh token API: error message');
        $this->authClient->getAccessToken('oldToken');
    }

    public function test_getAccessToken_should_throw_Strava_auth_client_error_if_api_returns_invalid_json()
    {
        $this->stravaApiContext();

        $this->httpClient->request(Argument::any(), Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json; charset=utf-8'],
                    'invalid'
                )
            );

        $this->logger->error('Strava auth client error: Unable to parse JSON response body')
            ->shouldBeCalled();

        $this->expectException(StravaAuthClientException::class);
        $this->expectExceptionMessage('Strava auth client error: Unable to parse JSON response body');
        $this->authClient->getAccessToken('oldToken');
    }

    public function test_getAccessToken_should_throw_Strava_auth_client_error_if_token_missing_from_response()
    {
        $this->stravaApiContext();

        $this->httpClient->request(Argument::any(), Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json; charset=utf-8'],
                    json_encode((object) [
                        "access_token" => "a5bb054e-5205-41fe-be77-8f6e45e1e4d5",
                    ])
                )
            );

        $this->logger->error('Strava auth client error: Response does not contain auth tokens')
            ->shouldBeCalled();

        $this->expectException(StravaAuthClientException::class);
        $this->expectExceptionMessage('Strava auth client error: Response does not contain auth tokens');
        $this->authClient->getAccessToken('oldToken');
    }

    public function authResponseBody()
    {
        return json_encode((object) [
            "token_type" => "Bearer",
            "expires_at" => 1589153055,
            "expires_in" => 21600,
            "refresh_token" => "05e6c43c-7751-41f5-be29-7fc6d254f677",
            "access_token" => "a5bb054e-5205-41fe-be77-8f6e45e1e4d5",
            "athlete" => (object) [
                "id" => 1118050,
                "username" => null,
                "resource_state" => 2,
                "firstname" => "Michael",
                "lastname" => "Wheeler",
                "city" => "Birmingham",
                "state" => "England",
                "country" => "United Kingdom",
                "sex" => "M",
                "premium" => false,
                "summit" => false,
                "created_at" => "2012-09-17T18:21:28Z",
                "updated_at" => "2020-05-03T16:33:33Z",
                "badge_type_id" => 0,
                "profile_medium" => "https://rjfurhfjsosjd.cloudfront.net/medium.jpg",
                "profile" => "https://sergservesdd.cloudfront.net/large.jpg",
                "friend" => null,
                "follower" => null
            ]
        ]);
    }

    private function stravaApiContext(): void
    {
        $this->context->getClientId()->shouldBeCalled()->willReturn('test');
        $this->context->getClientSecret()->shouldBeCalled()->willReturn('886e80d1-4f64-46ad-a509-f2f682665dbf');
        $this->context->getBaseUri()->shouldBeCalled()->willReturn('https://www.strava.com/api/v3/');
    }
}
