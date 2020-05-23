<?php

namespace CycleSaver\Infrastructure\Tfl\Client;

use CycleSaver\Domain\Entities\PTJourney;
use CycleSaver\Infrastructure\Tfl\Exception\TflClientException;
use DateInterval;
use DateTimeInterface;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class TflApiClient
{
    private TflContext $context;
    private ClientInterface $client;
    private LoggerInterface $logger;
    private DateTimeInterface $dateTime;

    public function __construct(
        TflContext $context,
        ClientInterface $client,
        LoggerInterface $logger,
        DateTimeInterface $dateTime
    ) {
        $this->context = $context;
        $this->client = $client;
        $this->logger = $logger;
        $this->dateTime = $dateTime;
    }

    /**
     * @param array $startLatLng
     * @param array $endLatLng
     * @return PTJourney
     * @throws TflClientException
     */
    public function getPTJourney(array $startLatLng, array $endLatLng): PTJourney
    {
        $startLatLng = implode(',', $startLatLng);
        $endLatLng = implode(',', $endLatLng);
        $startDate = date('Ymd', strtotime("next Monday", $this->dateTime->getTimestamp()));

        $response = $this->makeRequest(
            'GET',
            "/Journey/JourneyResults/{$startLatLng}/to/{$endLatLng}",
            [
                'query' =>
                    [
                        'nationalSearch' => 'true',
                        'date' => $startDate,
                        'time' => '0900',
                    ]
            ]
        );

        try {
            return $this->parsePTJourneyResponse($response);
        } catch (InvalidArgumentException $e) {
            $this->logger->error("TFL client was unable to parse activities response: {$e->getMessage()}");
            throw new TFLClientException(
                "TFL client was unable to parse activities response: {$e->getMessage()}"
            );
        }
    }

    /**
     * @param string $method
     * @param string $path
     * @param array|null $options
     * @return ResponseInterface
     * @throws TflClientException
     */
    private function makeRequest(string $method, string $path, array $options = null): ResponseInterface
    {
        $uri = new Uri(rtrim($this->context->getBaseUri(), '/') . $path);

        $options['query'] += [
            'app_id' => $this->context->getClientId(),
            'app_key' => $this->context->getClientKey()
        ];

        try {
            return $this->client->request(
                $method,
                $uri,
                $options
            );
        } catch (Throwable $e) {
            $this->logger->error("TFL client error when calling TFL API: {$e->getMessage()}");
            throw new TFLClientException(
                "TFL client error when calling TFL API: {$e->getMessage()}"
            );
        }
    }

    /**
     * @param $response
     * @return PTJourney
     */
    private function parsePTJourneyResponse($response): PTJourney
    {
        $body = json_decode($response->getBody()->getContents());

        if ($body === null) {
            throw new InvalidArgumentException('Body is not in valid JSON format');
        }

        if (!isset($body->journeys) || !is_array($body->journeys)) {
            throw new InvalidArgumentException('Response does not contain an array of journeys');
        }

        foreach ($body->journeys as $journey) {
            if (
                !isset($journey->fare) ||
                !isset($journey->fare->totalCost) ||
                !isset($journey->duration)
            ) {
                $this->logger->debug('TFL public transport journey is missing required field');
                continue;
            }

            try {
                $durationSeconds = $journey->duration * 60;
                $duration = new DateInterval("PT{$durationSeconds}S");
            } catch (Exception $e) {
                throw new InvalidArgumentException('Invalid duration format');
            }

            $cost = $journey->fare->totalCost / 100;

            return new PTJourney(
                $cost,
                $duration
            );
        }

        throw new InvalidArgumentException('Response does not contain any journeys with a fare and duration');
    }
}
