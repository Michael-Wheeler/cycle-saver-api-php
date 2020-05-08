<?php

namespace CycleSaver\Infrastructure;

use CycleSaver\Domain\Repository\UserRepositoryInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Ramsey\Uuid\Uuid;

class UserRepository implements UserRepositoryInterface
{
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

    public function save($user)
    {
        $this->logger->log(LogLevel::DEBUG, "############# SAVE METHOD ##################\n");

        $userArray = [
            '_id' => new ObjectId,
            'email' => $user->getEmail(),
            'password' => $user->getPassword()
        ];

        $bulk = new BulkWrite();
        $bulk->insert($userArray);

        try {
            $result = $this->manager->executeBulkWrite('cyclesaver.users', $bulk);
            var_dump('##SUCCESS##');
            $this->logger->log(LogLevel::DEBUG, 'Inserted count: ' . $result->getInsertedCount() . "\n");

            foreach ($result->getWriteErrors() as $error) {
                $this->logger->log(LogLevel::DEBUG, $error->getMessage() . "\n");
            }

//            throw new \Exception('Could not add user to DB');
        } catch (\Exception $e) {
            $this->logger->log(LogLevel::ERROR, $e->getMessage() . "\n");
            $this->logger->log(LogLevel::ERROR, (string) $e->getCode() . "\n");
            $this->logger->log(LogLevel::DEBUG, "############# END ##################\n");
        }
//        $collection->insertOne([
//            'username' => 'repo',
//            'email' => 'client@example.com',
//            'name' => 'Repo User',
//        ]);

    }
}
