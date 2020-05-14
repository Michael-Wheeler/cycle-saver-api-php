<?php

namespace CycleSaver\Infrastructure\Strava\Client;

use CycleSaver\Domain\Entities\User;
use CycleSaver\Infrastructure\Strava\Exception\StravaAuthClientException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Uri;
use Opia\Hub\Domain\Reward\Processing\NeoCurrency\Exception\NeoCurrencyClientException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class StravaApiAuthClient
{
    private StravaContext $context;
    private ClientInterface $client;
    private LoggerInterface $logger;
    private CacheItemPoolInterface $cache;


    public function __construct(
        StravaContext $context,
        ClientInterface $client,
        LoggerInterface $logger,
        CacheItemPoolInterface $cache
    ) {
        $this->context = $context;
        $this->client = $client;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * @param User $user
     * @return string
     */
    public function getAccessToken(User $user): string
    {
        //TODO Implement caching lib
        try {
            $tokenCache = $this->cache->getItem("strava-access-token-{$user->getId()}");
        } catch (InvalidArgumentException $e) {
            throw new StravaAuthClientException(
                "Unable to retrieve Strava user '{$user->getId()}' access token from cache: {$e->getMessage()}"
            );
        }

        if ($tokenCache->isHit()) {
            return $tokenCache->get();
        }

        [$accessToken, $refreshToken] = $this->refreshAccessToken($user->getRefreshToken());

        $user->setRefreshToken($refreshToken);

        $tokenCache->set($accessToken)->expiresAfter(new DateInterval('PT21540S'));
        $this->cache->save($tokenCache);

        return $accessToken;
    }

    /**
     * @param string $refreshToken
     * @return string[] [Access Token, Refresh Token]
     */
    private function refreshAccessToken(string $refreshToken): array
    {

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
            $this->logger->error("Strava auth client error when calling Strava API: {$e->getMessage()}");
            throw new StravaAuthClientException("Strava auth client error when calling Strava API: {$e->getMessage()}");
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

        return [
            'access_token' => $body->access_token,
            'refresh_token' => $body->refresh_token
        ];
    }
}
