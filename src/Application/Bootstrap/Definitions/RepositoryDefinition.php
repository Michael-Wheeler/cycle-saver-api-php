<?php

namespace CycleSaver\Application\Bootstrap\Definitions;

use CycleSaver\Domain\Repository\CommuteRepositoryInterface;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use CycleSaver\Infrastructure\CommuteRepository;
use CycleSaver\Infrastructure\UserRepository;
use DI\Container;
use MongoDB\Database;
use MongoDB\Driver\ClientEncryption;
use Psr\Log\LoggerInterface;

class RepositoryDefinition implements ServiceDefinition
{
    public static function getDefinitions(): array
    {
        return [
            UserRepositoryInterface::class => function (Container $c) {
                $database = $c->get(Database::class);
                $key = $password = getenv('MONGO_KEY');


                $database->createCollection('users', [
                    'validator' => [
                        '$jsonSchema' => [
                            'bsonType' => 'object',
                            'properties' => [
                                'password' => [
                                    'encrypt' => [
                                        'keyId' => [$key],
                                        'bsonType' => 'string',
                                        'algorithm' => ClientEncryption::AEAD_AES_256_CBC_HMAC_SHA_512_DETERMINISTIC,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]);

                return new UserRepository(
                    $database,
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
