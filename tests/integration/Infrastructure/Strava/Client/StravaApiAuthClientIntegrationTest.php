<?php

namespace CycleSaver\Infrastructure\Strava\Client;

use CycleSaver\Test\IntegrationTestCase;

class StravaApiAuthClientIntegrationTest extends IntegrationTestCase
{
    private StravaApiAuthClient $authClient;

    protected function setUp(): void
    {
        $this->markTestSkipped('No active sandbox environment for Stava integration tests.');

        parent::setUp();
        $this->authClient = $this->container->get(StravaApiAuthClient::class);
    }

    public function test_getAccessToken_should_call_strava_api_and_parse_auth_tokens()
    {
        $tokens = $this->authClient->authoriseUser('e2298a240a530ac572295b33957a7e1356cf31ac');

        $this->assertCount(2, $tokens);
        $this->assertNotNull($tokens['access_token']);
        $this->assertNotNull($tokens['refresh_token']);
    }
}
