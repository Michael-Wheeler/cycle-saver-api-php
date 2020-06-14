<?php

namespace CycleSaver\Infrastructure\Tfl\Client;

use CycleSaver\Test\ThirdPartyTestCase;

class TflApiClientIntegrationTest extends ThirdPartyTestCase
{
    private TflApiClient $client;

    public function setUp(): void
    {
        $this->markTestSkipped('More work needs to be done to stabilise the TFL commute costs from day to day.');

        parent::setUp();

        $this->client = $this->container->get(TflApiClient::class);
    }

    public function test_createPTJourney_should_call_tfl_api_and_parse_activity_repsonse()
    {
        $journey = $this->client->createPTJourney(
            [51.525640, -0.087604],
            [51.478873, -0.026715]
        );

        $this->assertEquals(2640, $journey->getDuration()->s);
        $this->assertEquals(4.70, $journey->getCost());
    }
}
