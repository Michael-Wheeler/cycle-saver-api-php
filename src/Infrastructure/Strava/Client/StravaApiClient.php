<?php

namespace CycleSaver\Infrastructure\Strava\Client;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

class StravaApiClient
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
}
