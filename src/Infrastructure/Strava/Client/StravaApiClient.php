<?php

namespace CycleSaver\Infrastructure\Strava\Client;

use CycleSaver\Domain\Entities\StravaActivity;
use CycleSaver\Domain\Entities\User;
use CycleSaver\Infrastructure\Strava\Exception\StravaAuthClientException;
use CycleSaver\Infrastructure\Strava\Exception\StravaClientException;
use DateTimeImmutable;
use Generator;
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
     * @param User $user
     * @param int $page
     * @return Generator|StravaActivity[]
     * @throws StravaAuthClientException
     * @throws StravaClientException
     */
    public function getActivities(User $user, int $page = 1): Generator
    {
        $response = $this->makeRequest(
            'GET',
            '/activities',
            $user,
            ['query' => ['page' => $page]]
        );

        $activities = $this->parseActivitiesResponse($response);

        foreach ($activities as $activity) {
            yield $activity;
        }

        while ($activities !== []) {
            $this->getActivities($user, $page++);
        }
    }

    /**
     * @param string $method
     * @param string $path
     * @param User $user
     * @param array|null $options
     * @return ResponseInterface
     * @throws StravaAuthClientException
     * @throws StravaClientException
     */
    private function makeRequest(
        string $method,
        string $path,
        User $user,
        array $options = null
    ): ResponseInterface {
        $accessToken = $this->authClient->getAccessToken($user);

        $uri = new Uri(rtrim($this->context->getBaseUri(), '/') . $path);

        $options['parameters'] = ['Authorization' => 'bearer ' . $accessToken];

        try {
            return $this->client->request(
                $method,
                $uri,
                $options
            );
        } catch (Throwable $e) {
            throw new StravaClientException(
                "Strava client error when calling Strava API: {$e->getMessage()}"
            );
        }
    }

    /**
     * @param ResponseInterface $response
     * @return StravaActivity[]
     * @throws StravaClientException
     */
    private function parseActivitiesResponse(ResponseInterface $response): array
    {
        $activities = json_decode($response->getBody()->getContents());

        if ($activities === null || !is_array($activities)) {
            throw new StravaClientException('Strava API has returned an invalid JSON body');
        }

        $activities = [];

        foreach ($activities as $activity) {
            try {
                $activities[] = $this->parseActivity($activity);
            } catch (InvalidArgumentException $e) {
                $this->logger->error("Unable to parse Strava activity: {$e->getMessage()}");
                continue;
            }
        }

        return $activities;
    }

    /**
     * @param object $activity
     * @return StravaActivity
     */
    private function parseActivity(object $activity): StravaActivity
    {
        if (
            !isset($activity->commute) ||
            !isset($activity->start_latlng) ||
            !isset($activity->end_latlng) ||
            !isset($activity->elapsed_time) ||
            !isset($activity->start_date_local) ||
            !isset($activity->distance)
        ) {
            throw new InvalidArgumentException('Strava activity is missing required field');
        }

        if (!$activity->commute) {
            throw new InvalidArgumentException('Strava activity is missing required field');
        }

        $startDate = DateTimeImmutable::createFromFormat(DATE_ISO8601, $activity->start_date_local);

        try {
            $duration = new \DateInterval("PT{$activity->elapsed_time}S");
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Strava activity has invalid duration format');
        }

        if (!$startDate) {
            throw new InvalidArgumentException('Strava activity has invalid start date format');
        }

        return new StravaActivity(
            $activity->start_latlng,
            $activity->end_latlng,
            $startDate,
            $duration,
            floor($activity->distance)
        );
    }
}
