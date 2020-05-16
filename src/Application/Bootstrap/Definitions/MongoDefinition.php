<?php

namespace CycleSaver\Application\Bootstrap\Definitions;

use CycleSaver\Application\Bootstrap\ContainerException;
use DI\Container;
use MongoDB\Database;
use MongoDB\Driver\Manager;

class MongoDefinition implements ServiceDefinition
{
    public static function getDefinitions(): array
    {
        return [
            Manager::class => function () {
                $username = getenv('MONGO_ADMIN');
                $password = getenv('MONGO_ADMIN_PASS');

                if (!$username || !$password) {
                    throw new ContainerException('Unable to retrieve MongoDB environment variables');
                }

                return new Manager("mongodb://${username}:${password}@mongo:27017/");
            },
            Database::class => function (Container $c) {
                $manager = $c->get(Manager::class);

                if (!$username || !$password) {
                    throw new ContainerException('Unable to retrieve MongoDB dependencies');
                }

                return new Database(
                    $manager,
                    'cyclesaver'
                );
            },
        ];
    }
}
