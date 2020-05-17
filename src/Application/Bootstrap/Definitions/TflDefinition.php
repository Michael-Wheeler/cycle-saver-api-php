<?php

namespace CycleSaver\Application\Bootstrap\Definitions;

use CycleSaver\Application\Bootstrap\ContainerException;
use CycleSaver\Infrastructure\Tfl\Client\TflContext;

class TflDefinition implements ServiceDefinition
{
    public static function getDefinitions(): array
    {
        return [
            TflContext::class => function () {
                $baseUri = getenv('TFL_BASE_URI');
                $clientID = getenv('TFL_CLIENT_ID');
                $clientKey = getenv('TFL_CLIENT_KEY');

                if (!$baseUri || !$clientID || !$clientKey) {
                    throw new ContainerException('Unable to retrieve FTL environment variables');
                }

                return new TflContext($clientID, $clientKey, $baseUri);
            }
        ];
    }
}
