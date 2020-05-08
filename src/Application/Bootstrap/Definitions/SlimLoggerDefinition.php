<?php

namespace CycleSaver\Application\Bootstrap\Definitions;

use Psr\Log\LoggerInterface;
use Slim\Logger;

class SlimLoggerDefinition implements ServiceDefinition
{
    public static function getDefinitions(): array
    {
        return [
            LoggerInterface::class => function () {
                return new Logger();
            },
        ];
    }
}
