<?php

namespace CycleSaver\Infrastructure\Strava\Client;

use CycleSaver\Domain\Entities\User;
use CycleSaver\Infrastructure\Strava\Exception\StravaAuthClientException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class StravaApiAuthClient
{
    private StravaContext $context;
    private ClientInterface $client;
    private LoggerInterface $logger;

    public function __construct(
        StravaContext $context,
        ClientInterface $client,
        LoggerInterface $logger
    ) {
        $this->context = $context;
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * Get access token and update refresh token
     *
     * @param User $user
     * @return string
     * @throws StravaAuthClientException
     */
    public function getAccessToken(User $user): string
    {
        if ($user->getRefreshToken() === null) {
            $this->logger->error('User refresh token required to get new Strava access token');
            throw new StravaAuthClientException('User refresh token required to get new Strava access token');
        }

        [$accessToken, $refreshToken] = $this->refreshAccessToken($user->getRefreshToken());

        $user->setRefreshToken($refreshToken);

        return $accessToken;
    }

    /**
     * Swap refresh token for access token and new refresh token
     *
     * @param string $refreshToken
     * @return string[] [Access Token, Refresh Token]
     * @throws StravaAuthClientException
     */
    private function refreshAccessToken(string $refreshToken): array
    {
        $uri = new Uri(rtrim($this->context->getBaseUri(), '/') . '/oauth/token');

        try {
            $response = $this->client->request(
                'POST',
                $uri,
                [
                    'query' => [
                        'client_id' => $this->context->getClientId(),
                        'client_secret' => $this->context->getClientSecret(),
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $refreshToken
                    ]
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error("Strava auth client error when calling Strava refresh token API: {$e->getMessage()}");
            throw new StravaAuthClientException(
                "Strava auth client error when calling Strava refresh token API: {$e->getMessage()}"
            );
        }

        return $this->parseAuthTokens($response);
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
        $uri = new Uri(rtrim($this->context->getBaseUri(), '/') . '/oauth/token');

        try {
            $response = $this->client->request(
                'POST',
                $uri,
                [
                    'query' => [
                        'client_id' => $this->context->getClientId(),
                        'client_secret' => $this->context->getClientSecret(),
                        'code' => $authCode,
                        'grant_type' => 'authorization_code',
                    ]
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error("Strava auth client error when calling Strava auth code API: {$e->getMessage()}");
            throw new StravaAuthClientException(
                "Strava auth client error when calling Strava auth code API: {$e->getMessage()}"
            );
        }

        return $this->parseAuthTokens($response);
    }

    /**
     * @param ResponseInterface $response
     * @return string[] [Access Token, Refresh Token]
     * @throws StravaAuthClientException
     */
    private function parseAuthTokens(ResponseInterface $response): array
    {
        $body = json_decode($response->getBody()->getContents());

        if ($body === null) {
            $this->logger->error('Strava auth client error: Unable to parse JSON response body');
            throw new StravaAuthClientException(
                'Strava auth client error: Unable to parse JSON response body'
            );
        }

        if (!isset($body->refresh_token) || !isset($body->access_token)) {
            $this->logger->error('Strava auth client error: Response does not contain auth tokens');
            throw new StravaAuthClientException(
                'Strava auth client error: Response does not contain auth tokens'
            );
        }

        return [$body->access_token, $body->refresh_token];
    }
}
