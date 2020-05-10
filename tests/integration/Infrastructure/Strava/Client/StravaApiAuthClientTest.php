<?php

namespace CycleSaver\Infrastructure\Strava\Client;

use CycleSaver\Test\IntegrationTestCase;

class StravaApiAuthClientTest extends IntegrationTestCase
{
    private StravaApiAuthClient $authClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authClient = $this->container->get(StravaApiAuthClient::class);
    }

    public function test_getAccessToken_should_call_strava_api_and_parse_auth_tokens()
    {
        $tokens = $this->authClient->getAccessToken('e2298a240a530ac572295b33957a7e1356cf31ac');

        $this->assertCount(2, $tokens);
        $this->assertNotNull($tokens['access_token']);
        $this->assertNotNull($tokens['refresh_token']);
    }
}
