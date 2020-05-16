<?php

namespace CycleSaver\Application\Bootstrap\Definitions;

use CycleSaver\Domain\Repository\ActivityRepositoryInterface;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use CycleSaver\Infrastructure\ActivityRepository;
use CycleSaver\Infrastructure\UserRepository;
use DI\Container;
use MongoDB\Driver\Manager;
use Psr\Log\LoggerInterface;

class RepositoryDefinition implements ServiceDefinition
{
    public static function getDefinitions(): array
    {
        return [
            UserRepositoryInterface::class => function (Container $c) {
                return new UserRepository(
                    $c->get(Manager::class),
                    $c->get(LoggerInterface::class)
                );
            },
            ActivityRepositoryInterface::class => function (Container $c) {
                return new ActivityRepository(
                    $c->get(Manager::class),
                    $c->get(LoggerInterface::class)
                );
            },
        ];
    }
}
