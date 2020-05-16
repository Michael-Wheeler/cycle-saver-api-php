<?php

namespace CycleSaver\Infrastructure\Strava;

use CycleSaver\Domain\Entities\Activity;
use CycleSaver\Domain\Entities\User;
use CycleSaver\Infrastructure\Strava\Client\StravaApiAuthClient;
use CycleSaver\Infrastructure\Strava\Client\StravaApiClient;
use CycleSaver\Infrastructure\Strava\Exception\StravaAuthClientException;
use CycleSaver\Infrastructure\Strava\Exception\StravaClientException;

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
     * Swap authorisation code for access token and refresh token
     *
     * @param string $authCode
     * @return string[] [Access Token, Refresh Token]
     * @throws StravaAuthClientException
     */
    public function authoriseUser(string $authCode): array
    {
        return $this->authClient->authoriseUser($authCode);
    }

    /**
     * @param string $refreshToken
     * @throws StravaAuthClientException
     */
    public function refreshAccessToken(string $refreshToken)
    {
        [$accessToken, $refreshToken] = $this->authClient->getAccessToken($refreshToken);
    }

    /**
     * @param string $accessToken
     * @return Activity[]
     * @throws StravaClientException
     */
    public function getActivities(string $accessToken): array
    {
        return $this->client->getActivities($accessToken);
    }
}
