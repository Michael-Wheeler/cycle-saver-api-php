<?php

namespace CycleSaver\Infrastructure\Tfl;

use CycleSaver\Domain\Entities\PTJourney;
use CycleSaver\Infrastructure\Tfl\Client\TflApiClient;

class TflRepository
{
    private TflApiClient $client;

    public function __construct(TflApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param array $startLatLng
     * @param array $endLatLng
     * @return PTJourney
     * @throws Exception\TflClientException
     */
    public function getPTJourney(array $startLatLng, array $endLatLng): PTJourney
    {
        return $this->client->getPTJourney($startLatLng, $endLatLng);
    }
}
