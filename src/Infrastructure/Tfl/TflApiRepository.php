<?php

namespace CycleSaver\Infrastructure\Tfl;

use CycleSaver\Domain\Entities\PTJourney;
use CycleSaver\Domain\Repository\RepositoryException;
use CycleSaver\Infrastructure\Tfl\Client\TflApiClient;
use CycleSaver\Infrastructure\Tfl\Exception\TflClientException;

class TflApiRepository
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
     * @throws RepositoryException
     */
    public function getPTJourney(array $startLatLng, array $endLatLng): PTJourney
    {
        try {
            return $this->client->createPTJourney($startLatLng, $endLatLng);
        } catch (TflClientException $e) {
            throw new RepositoryException('Unable to create public transport journey');
        }
    }

    /**
     * @param array $startEndCoordinates [[Start Lat Lng, End Lat Lng]]
     * @return PTJourney[]
     */
    public function getPTJourneys(array $startEndCoordinates): array
    {
        return $this->client->createPTJourneys($startEndCoordinates);
    }
}
