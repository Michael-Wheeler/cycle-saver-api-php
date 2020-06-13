<?php

namespace CycleSaver\Infrastructure;

use CycleSaver\Domain\Entities\User;
use CycleSaver\Domain\Repository\RepositoryException;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\Driver\Exception\RuntimeException as DriverRuntimeException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UserRepository implements UserRepositoryInterface
{
    private string $collectionName = 'users';
    private Collection $collection;
    private LoggerInterface $logger;

    public function __construct(Database $database, LoggerInterface $logger)
    {
        $this->collection = $database->selectCollection($this->collectionName);
        $this->logger = $logger;
    }

    public function save(User $user): UuidInterface
    {
        $id = $user->getId() ?? Uuid::uuid4();

        $userArray = [
            '_id' => (string) $id,
            'email' => $user->getEmail(),
            'password' => $user->getPassword()
        ];

        try {
            $this->collection->insertOne($userArray);
        } catch (\InvalidArgumentException | DriverRuntimeException $e) {
            throw new RepositoryException('Could not add user to collection: ' . $e->getMessage());
        }

        return $id;
    }
}
