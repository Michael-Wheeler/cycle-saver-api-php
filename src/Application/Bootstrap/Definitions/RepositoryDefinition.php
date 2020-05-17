<?php

namespace CycleSaver\Application\Bootstrap\Definitions;

use CycleSaver\Domain\Repository\CommuteRepositoryInterface;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use CycleSaver\Infrastructure\CommuteRepository;
use CycleSaver\Infrastructure\UserRepository;
use DI\Container;
use MongoDB\Database;
use Psr\Log\LoggerInterface;

class RepositoryDefinition implements ServiceDefinition
{
    public static function getDefinitions(): array
    {
        return [
            UserRepositoryInterface::class => function (Container $c) {
                return new UserRepository(
                    $c->get(Database::class),
                    $c->get(LoggerInterface::class)
                );
            },
            CommuteRepositoryInterface::class => function (Container $c) {
                return new CommuteRepository(
                    $c->get(Database::class),
                    $c->get(LoggerInterface::class)
                );
            },
        ];
    }
}
