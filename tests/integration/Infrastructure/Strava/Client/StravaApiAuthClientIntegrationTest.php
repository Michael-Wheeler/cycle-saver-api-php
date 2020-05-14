<?php

namespace CycleSaver\Infrastructure\Strava\Client;

use CycleSaver\Domain\Entities\User;
use CycleSaver\Test\IntegrationTestCase;

class StravaApiAuthClientIntegrationTest extends IntegrationTestCase
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

        $tokens = $this->authClient->authoriseUser('e2298a240a530ac572295b33957a7e1356cf31ac');

        $this->assertCount(2, $tokens);
        $this->assertNotNull($tokens['access_token']);
        $this->assertNotNull($tokens['refresh_token']);
    }

    public function test_getAccessToken_should_call_strava_api_and_parse_auth_tokens()
    {
        $user = new User('test@test.com', 'password', null, '9acca5464ab5d8c6f4870dc4b0cb79c290adcf78');

        [$accessToken, $refreshToken] = $this->authClient->getAccessToken($user);

        $this->assertNotNull($accessToken);
        $this->assertNotNull($refreshToken);
    }
}
