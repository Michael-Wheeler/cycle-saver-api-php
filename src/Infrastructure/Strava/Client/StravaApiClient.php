<?php

namespace CycleSaver\Infrastructure\Strava\Client;

use CycleSaver\Domain\Entities\StravaActivity;
use CycleSaver\Infrastructure\Strava\Exception\StravaClientException;
use DateInterval;
use DateTimeImmutable;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class StravaApiClient
{
    private StravaContext $context;
    private ClientInterface $client;
    private StravaApiAuthClient $authClient;
    private LoggerInterface $logger;

    public function __construct(
        StravaContext $context,
        ClientInterface $client,
        StravaApiAuthClient $authClient,
        LoggerInterface $logger
    ) {
        $this->context = $context;
        $this->client = $client;
        $this->authClient = $authClient;
        $this->logger = $logger;
    }

    /**
     * @param string $accessToken
     * @return StravaActivity[]
     * @throws StravaClientException
     */
    public function getActivities(string $accessToken): array
    {
        $totalActivities = [];
        $page = 1;
        do {
            $activities = $this->makeActivitiesRequest($accessToken, $page);
            $count = count($activities);

            $totalActivities = array_merge($totalActivities, $activities);

            $page++;
        } while ($count !== 0);

        return $totalActivities;
    }

    /**
     * @param string $accessToken
     * @param int $page
     * @return StravaActivity[]
     * @throws StravaClientException
     */
    private function makeActivitiesRequest(string $accessToken, int $page = 1): array
    {
        $response = $this->makeRequest(
            'GET',
            '/activities',
            $accessToken,
            ['query' => ['page' => $page]]
        );

        try {
            return $this->parseActivitiesResponse($response);
        } catch (InvalidArgumentException $e) {
            $this->logger->error("Strava client was unable to parse activities response: {$e->getMessage()}");
            throw new StravaClientException(
                "Strava client was unable to parse activities response: {$e->getMessage()}"
            );
        }
    }

    /**
     * @param string $method
     * @param string $path
     * @param string $accessToken
     * @param array|null $options
     * @return ResponseInterface
     * @throws StravaClientException
     */
    private function makeRequest(
        string $method,
        string $path,
        string $accessToken,
        array $options = null
    ): ResponseInterface {
        $uri = new Uri(rtrim($this->context->getBaseUri(), '/') . $path);

        $options['headers'] = ['Authorization' => 'Bearer ' . $accessToken];

        try {
            return $this->client->request(
                $method,
                $uri,
                $options
            );
        } catch (Throwable $e) {
            $this->logger->error("Strava client error when calling Strava API: {$e->getMessage()}");
            throw new StravaClientException(
                "Strava client error when calling Strava API: {$e->getMessage()}"
            );
        }
    }

    /**
     * @param ResponseInterface $response
     * @return StravaActivity[]
     */
    private function parseActivitiesResponse(ResponseInterface $response): array
    {
        $activitiesBody = json_decode($response->getBody()->getContents());

        if ($activitiesBody === null || !is_array($activitiesBody)) {
            throw new InvalidArgumentException('Response body is not in a valid JSON array format');
        }

        $activities = [];
        foreach ($activitiesBody as $activity) {
            try {
                $activities[] = $this->parseActivity($activity);
            } catch (InvalidArgumentException $e) {
                $this->logger->error("Unable to parse Strava activity: {$e->getMessage()}");
                continue;
            }
        }

        return array_filter($activities);
    }

    /**
     * @param object $activity
     * @return StravaActivity
     */
    private function parseActivity(object $activity): ?StravaActivity
    {
        $missingProperties = $this->validateProperties(
            $activity,
            [
                'commute',
                'start_latlng',
                'end_latlng',
                'elapsed_time',
                'start_date_local',
                'distance'
            ]
        );

        if ($missingProperties !== []) {
            throw new InvalidArgumentException('Strava activity is missing required fields: ' .
                implode(', ', $missingProperties));
        }

        if (!$activity->commute) {
            return null;
        }

        if ($activity->start_latlng === $activity->end_latlng) {
            $this->logger->debug('Cannot use Strava activities that start and end at the same location');
            return null;
        }

        $startDate = DateTimeImmutable::createFromFormat(DATE_ISO8601, $activity->start_date_local);
        if (!$startDate) {
            throw new InvalidArgumentException('Invalid start date format');
        }

        try {
            $duration = new DateInterval("PT{$activity->elapsed_time}S");
        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid duration format');
        }

        return new StravaActivity(
            $activity->start_latlng,
            $activity->end_latlng,
            $startDate,
            $duration,
            floor($activity->distance),
            null
        );
    }

    /**
     * @param object $object
     * @param string[] $properties
     * @return string[]
     */
    private function validateProperties(object $object, array $properties): array
    {
        return array_filter($properties, function (string $property) use ($object) {
            return !isset($object->$property);
        });
    }
}
