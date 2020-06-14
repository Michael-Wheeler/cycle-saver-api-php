<?php

namespace CycleSaver\Infrastructure;

use CycleSaver\Domain\Entities\User;
use CycleSaver\Domain\Repository\RepositoryException;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\Driver\Exception\RuntimeException as DriverRuntimeException;
use MongoDB\Exception\UnsupportedException;
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

    public function save(User $user): void
    {
        $userArray = [
            '_id' => (string) $user->getId(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword()
        ];

        try {
            $this->collection->insertOne($userArray);
        } catch (\InvalidArgumentException | DriverRuntimeException $e) {
            throw new RepositoryException('Could not add user to database: ' . $e->getMessage());
        }
    }

    public function getUserById(UuidInterface $id): User
    {
        try {
            $document = $this->collection->findOne(['_id' => (string) $id]);

            return $this->documentToUser($document);
        } catch (UnsupportedException | \MongoDB\Exception\InvalidArgumentException | DriverRuntimeException $e) {
            throw new RepositoryException('Error when retrieving user from database: ' . $e->getMessage());
        }
    }

    private function documentToUser(object $document): User
    {
        return new User(
            Uuid::fromString($document->_id),
            $document->email ?? null,
            $document->password ?? null
        );
    }
}
