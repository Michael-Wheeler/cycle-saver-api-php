<?php

namespace CycleSaver\Infrastructure\Strava\Client;

use CycleSaver\Test\IntegrationTestCase;

class StravaApiClientIntegrationTest extends IntegrationTestCase
{
    private StravaApiClient $client;

    protected function setUp(): void
    {
        $this->markTestSkipped('No active sandbox environment for Strava integration tests.');

        parent::setUp();
        $this->client = $this->container->get(StravaApiClient::class);
    }

    public function test_getActivities_should_call_strava_api_and_parse_activities_response()
    {
        $activities = $this->client->getActivities('f2e63533eceebe3a40f8f68caed23488f182e11d');

        $this->assertNotEmpty($activities);
    }
}
