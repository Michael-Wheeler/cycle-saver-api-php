<?php

namespace CycleSaver\Infrastructure;

use CycleSaver\Domain\Entities\User;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use Exception;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

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

    /**
     * @param User $user
     * @return UuidInterface
     * @throws Exception
     */
    public function save(User $user): UuidInterface
    {
        $id = property_exists($user, 'id') ? $user->getId() : null;
        $email = property_exists($user, 'email') ? $user->getEmail() : null;

        $userArray = [
            '_id' => (string) $id,
            'email' => $email,
            'password' => property_exists($user, 'password') ? $user->getPassword() : null
        ];

        $bulk = new BulkWrite();
        $bulk->insert($userArray);

        try {
            $this->manager->executeBulkWrite($this->userNamespace, $bulk);
        } catch (Exception $e) {
            throw new Exception('Could not add user to DB' . $e->getMessage());
        }

        return $id;
    }
}
