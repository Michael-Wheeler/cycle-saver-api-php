<?php

namespace CycleSaver\Application\Bootstrap\Definitions;

use CycleSaver\Application\Bootstrap\ContainerException;
use MongoDB\Driver\Manager;

class MongoDefinition implements ServiceDefinition
{
    public static function getDefinitions(): array
    {
        return [
            Manager::class => function () {
//                require __DIR__ . '/../../../../vendor/autoload.php';

                $username = getenv('MONGO_ADMIN');
                $password = getenv('MONGO_ADMIN_PASS');

                if (!$username || !$password) {
                    throw new ContainerException('Unable to retrieve MongoDB Admin username and password');
                }

                return new Manager("mongodb://${username}:${password}@mongo:27017/");
            }
        ];
    }
}
