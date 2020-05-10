<?php

namespace CycleSaver\Infrastructure\Strava\Client;

use CycleSaver\Infrastructure\Strava\Exception\StravaClientException;
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

    public function __construct(StravaContext $context, ClientInterface $client, LoggerInterface $logger)
    {
        $this->context = $context;
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * Swap authorisation code for access token and refresh token
     *
     * @param string $authCode
     * @return string[] [Access Token, Refresh Token]
     * @throws StravaClientException
     */
    public function getAccessToken(string $authCode): array
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
            $this->logger->error("Strava auth client error when calling Strava API: {$e->getMessage()}");
            throw new StravaClientException("Strava auth client error when calling Strava API: {$e->getMessage()}");
        }

        return $this->parseAuthTokens($response);
    }

    /**
     * @param ResponseInterface $response
     * @return string[] [Access Token, Refresh Token]
     * @throws StravaClientException
     */
    private function parseAuthTokens(ResponseInterface $response): array
    {
        $body = json_decode($response->getBody()->getContents());

        if ($body === null) {
            $this->logger->error('Strava auth client error: Unable to parse JSON response body');
            throw new StravaClientException(
                'Strava auth client error: Unable to parse JSON response body'
            );
        }

        if (!isset($body->refresh_token) || !isset($body->access_token)) {
            $this->logger->error('Strava auth client error: Response does not contain auth tokens');
            throw new StravaClientException(
                'Strava auth client error: Response does not contain auth tokens'
            );
        }

        return [
            'access_token' => $body->access_token,
            'refresh_token' => $body->refresh_token
        ];
    }
}
