<?php

namespace CycleSaver\Infrastructure;

use CycleSaver\Domain\Entities\User;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use Exception;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class UserRepository implements UserRepositoryInterface
{
    private string $userNamespace = 'cyclesaver.users';
    private Manager $manager;
    private LoggerInterface $logger;

    public function __construct(Manager $manager, LoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->logger = $logger;
    }

    public function getById(Uuid $id)
    {
        // TODO: Implement getById() method.
    }

    public function getAll()
    {
        // TODO: Implement getAll() method.
    }

    /**
     * @param User $user
     * @return ObjectId
     * @throws Exception
     */
    public function save(User $user)
    {
        $userArray = [
            '_id' => $id = new ObjectId(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword()
        ];

        $bulk = new BulkWrite();
        $bulk->insert($userArray);

        try {
            $this->manager->executeBulkWrite($this->userNamespace, $bulk);
            return $id;
        } catch (Exception $e) {
            throw new Exception('Could not add user to DB' . $e->getMessage());
        }
    }
}
