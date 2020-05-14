<?php

namespace CycleSaver\Infrastructure;

use CycleSaver\Infrastructure\Strava\Client\StravaApiAuthClient;
use CycleSaver\Infrastructure\Strava\Client\StravaApiClient;
use CycleSaver\Infrastructure\Strava\Exception\StravaAuthClientException;

class StravaRepository
{
    private StravaApiAuthClient $authClient;
    private StravaApiClient $client;

    public function __construct(StravaApiAuthClient $authClient, StravaApiClient $client)
    {
        $this->authClient = $authClient;
        $this->client = $client;
    }

    /**
     * @param string $authCode
     * @throws StravaAuthClientException
     */
    public function newUser(string $authCode)
    {
        $this->authClient->authoriseUser($authCode);

        $this->client->getActivities();
    }
}
