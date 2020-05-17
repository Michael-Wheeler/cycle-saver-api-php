<?php

namespace CycleSaver\Application\Bootstrap\Definitions;

use CycleSaver\Application\Bootstrap\ContainerException;
use DI\Container;
use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Driver\Exception\InvalidArgumentException as DriverInvalidArgumentException;
use MongoDB\Driver\Exception\RuntimeException as DriverRuntimeException;
use MongoDB\Exception\InvalidArgumentException;

class MongoDefinition implements ServiceDefinition
{
    public static function getDefinitions(): array
    {
        return array(
            Client::class => function () {
                $username = getenv('MONGO_ADMIN');
                $password = getenv('MONGO_ADMIN_PASS');

                if (!$username || !$password) {
                    throw new ContainerException('Unable to retrieve MongoDB client dependencies');
                }

                try {
                    return new Client(
                        $uri = "mongodb://mongo:27017/",
                        [
                            'username' => $username,
                            'password' => $password,
                        ]
                    );
                } catch (InvalidArgumentException | DriverInvalidArgumentException | DriverRuntimeException $e) {
                    throw new ContainerException("Could not instantiate MongoBD client: {$e->getMessage()}");
                }
            },
            Database::class => function (Container $c) {
                $client = $c->get(Client::class);

                if (!$client) {
                    throw new ContainerException('Unable to retrieve MongoDB database dependencies');
                }

                try {
                    return $client->selectDatabase('cyclesaver');
                } catch (\InvalidArgumentException $e) {
                    throw new ContainerException("Could not instantiate MongoBD database: {$e->getMessage()}");
                }
            }
        );
    }
}
