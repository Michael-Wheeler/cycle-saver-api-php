<?php

namespace CycleSaver\Infrastructure\Tfl\Client;

class TflContext
{
    private string $clientId;
    private string $clientKey;
    private string $baseUri;

    public function __construct(string $clientId, string $clientKey, string $baseUri)
    {
        $this->clientId = $clientId;
        $this->clientKey = $clientKey;
        $this->baseUri = $baseUri;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }
}
