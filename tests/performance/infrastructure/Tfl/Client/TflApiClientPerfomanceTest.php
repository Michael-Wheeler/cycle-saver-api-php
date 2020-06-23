<?php

namespace CycleSaver\Infrastructure\Tfl\Client;

use CycleSaver\IntegrationTestCase;

class TflApiClientPerfomanceTest extends IntegrationTestCase
{
    private TflApiClient $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->container->get(TflApiClient::class);
    }

    public function test_createPTJourney_performance()
    {
        foreach ($this->journeys() as $journey) {
            $journeys[] = $this->client->createPTJourney($journey['start_latlng'], $journey['end_latlng']);
        }

        $this->assertEquals(30, count($journeys));
    }

    public function test_createPTJourneys_performance()
    {
        $journeys = $this->client->createPTJourneys($this->journeys());

        $this->assertEquals(30, count($journeys));
    }

    private function journeys()
    {
        $journeys = [
            [
                'start_latlng' => [51.525640, -0.087604],
                'end_latlng' => [51.478873, -0.026715]
            ],
        ];

        for ($i = 0; $i < 30; $i++) {
            $journeys[] = $this->randomLondonCoordinates();
        }

        return $journeys;
    }

    private function randomLondonCoordinates(): array
    {
        return [
            'start_latlng' => [$this->randomLondonLat(), $this->randomLondonLng()],
            'end_latlng' => [$this->randomLondonLat(), $this->randomLondonLng()]
        ];
    }

    private function randomLondonLat()
    {
        return rand(5138, 5162) / 100;
    }

    private function randomLondonLng()
    {
        return rand(-303685, -320000) / 1000000;
    }
}
