<?php

namespace CycleSaver\Application\Bootstrap\Definitions;

use CycleSaver\Application\Bootstrap\ContainerException;
use CycleSaver\Infrastructure\Strava\Client\StravaApiAuthClient;
use CycleSaver\Infrastructure\Strava\Client\StravaApiClient;
use CycleSaver\Infrastructure\Strava\Client\StravaContext;
use CycleSaver\Infrastructure\StravaRepository;
use DI\Container;

class StravaInfrastructureDefinition implements ServiceDefinition
{
    public static function getDefinitions(): array
    {
        return [
            StravaContext::class => function () {
                $baseUri = getenv('STRAVA_BASE_URI');
                $clientID = getenv('STRAVA_CLIENT_ID');
                $clientSecret = getenv('STRAVA_CLIENT_SECRET');

                if (!$baseUri || !$clientID || !$clientSecret) {
                    throw new ContainerException('Unable to retrieve Strava environment variables');
                }

                return new StravaContext($baseUri, $clientID, $clientSecret);
            },
//            StravaRepository::class => function (Container $c) {
//                $authClient = $c->get(StravaApiAuthClient::class);
//                $client = $c->get(StravaApiClient::class);
//
//                if (!$authClient || !$client) {
//                    throw new ContainerException('Unable to retrieve Strava repository dependencies');
//                }
//
//                return new StravaRepository($authClient, $client);
//            }
        ];
    }
}
