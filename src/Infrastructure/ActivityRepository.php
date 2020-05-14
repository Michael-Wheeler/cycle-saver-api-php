<?php

namespace CycleSaver\Infrastructure;

use CycleSaver\Domain\Entities\User;
use CycleSaver\Domain\Repository\ActivityRepositoryInterface;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ActivityRepository implements ActivityRepositoryInterface
{
    private string $userNamespace = 'cyclesaver.activities';
    private Manager $manager;
    private LoggerInterface $logger;

    public function __construct(Manager $manager, LoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->logger = $logger;
    }

    public function getActivities(): array
    {
        // TODO: Implement getActivities() method.
    }

    /**
     * @param User $user
     * @return UuidInterface
     * @throws Exception
     */
    public function save(User $user): UuidInterface
    {
        $userArray = [
            '_id' => $id = $user->getId() ?? Uuid::uuid4(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword()
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
