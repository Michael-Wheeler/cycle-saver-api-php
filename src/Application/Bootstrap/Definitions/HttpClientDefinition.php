<?php

namespace CycleSaver\Application\Bootstrap\Definitions;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class HttpClientDefinition implements ServiceDefinition
{

    public static function getDefinitions(): array
    {
        return [
            ClientInterface::class => function () {
                return new Client();
            },
        ];
    }
}
