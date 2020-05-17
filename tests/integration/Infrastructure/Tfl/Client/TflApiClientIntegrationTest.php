<?php

namespace CycleSaver\Infrastructure\Tfl\Client;

use CycleSaver\Test\IntegrationTestCase;

class TflApiClientIntegrationTest extends IntegrationTestCase
{
    private TflApiClient $client;

    public function setUp(): void
    {
//        $this->markTestSkipped('TFL journeys unfortunately have different routes and fares day be day.');

        parent::setUp();
        $this->client = $this->container->get(TflApiClient::class);
    }

    public function test_getPTJourney_should_call_tfl_api_and_parse_activity_repsonse()
    {
        $journey = $this->client->getPTJourney(
            [51.525640, -0.087604],
            [51.478873, -0.026715]
        );

        $this->assertEquals(2640, $journey->getDuration()->s);
        $this->assertEquals(4.70, $journey->getCost());
    }
}
