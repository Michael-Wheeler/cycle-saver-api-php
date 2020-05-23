<?php

namespace CycleSaver\Application\Bootstrap\Definitions;

use DateTimeImmutable;
use DateTimeInterface;

class DateTimeDefinition implements ServiceDefinition
{
    public static function getDefinitions(): array
    {
        return [
            DateTimeInterface::class => function () {
                return new DateTimeImmutable();
            },
        ];
    }
}
