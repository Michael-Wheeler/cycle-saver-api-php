<?php

namespace CycleSaver\Infrastructure\Strava\Client;

use CycleSaver\Domain\Entities\User;
use CycleSaver\ThirdPartyTestCase;
use Ramsey\Uuid\Uuid;

class StravaApiAuthClientIntegrationTest extends ThirdPartyTestCase
{
    private StravaApiAuthClient $authClient;

    protected function setUp(): void
    {
        $this->markTestSkipped('No active sandbox environment for Strava integration tests.');

        parent::setUp();
        $this->authClient = $this->container->get(StravaApiAuthClient::class);
    }

    public function test_authoriseUser_should_call_strava_api_and_parse_auth_tokens()
    {
        [$accessToken, $refreshToken] = $this->authClient->authenticateUser('e0a045e4fc315db71997c97c1168f02994820977');

        $this->assertNotNull($accessToken);
        $this->assertNotNull($refreshToken);
    }

    public function test_getAccessToken_should_call_strava_api_and_parse_auth_tokens()
    {
        $user = (new User(Uuid::uuid4()))
            ->setEmail('test@test.com')
            ->setPassword('password')
            ->setRefreshToken('0dcd5922a58bc907ddd922bc926c6474fa09c319');

        $accessToken = $this->authClient->getAccessToken($user);

        $this->assertNotNull($accessToken);
        $this->assertNotNull($user->getRefreshToken());
        $this->assertNotEquals('0dcd5922a58bc907ddd922bc926c6474fa09c319', $user->getRefreshToken());
    }
}
