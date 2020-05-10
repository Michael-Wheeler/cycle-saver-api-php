<?php

namespace CycleSaver\Infrastructure\Strava\Client;

class StravaContext
{
    private string $baseUri;
    private string $clientId;
    private string $clientSecret;

    public function __construct(string $baseUri, string $clientId, string $clientSecret)
    {
        $this->baseUri = $baseUri;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }
}
