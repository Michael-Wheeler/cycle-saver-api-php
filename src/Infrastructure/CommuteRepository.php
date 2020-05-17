<?php

namespace CycleSaver\Infrastructure;

use CycleSaver\Domain\Entities\Commute;
use CycleSaver\Domain\Repository\CommuteRepositoryInterface;
use Exception;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CommuteRepository implements CommuteRepositoryInterface
{
    private string $userNamespace = 'cyclesaver.activities';
    private Manager $manager;
    private LoggerInterface $logger;

    public function __construct(Manager $manager, LoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->logger = $logger;
    }

    public function getCommutes(): array
    {
        // TODO: Implement getCommutes() method.
    }

    /**
     * @param Commute $commute
     * @return UuidInterface
     * @throws Exception
     */
    public function saveCommute(Commute $commute): UuidInterface
    {
        $id = $commute->getId() ?? Uuid::uuid4();

        $commuteArray = [
            '_id' => (string) $id,
            'user_id' => $commute->getUserId(),
            'start_latlng' => $commute->getStartLatLong(),
            'end_latlng' => $commute->getEndLatLong(),
            'start_date' => $commute->getStartDate(),
            'activity_duration' => $commute->getActivityDuration(),
            'public_transport_duration' => $commute->getPTDuration(),
            'public_transport_cost' => $commute->getPTCost(),
        ];

        $bulk = new BulkWrite();
        $bulk->insert($commuteArray);

        try {
            $this->manager->executeBulkWrite($this->userNamespace, $bulk);
        } catch (Exception $e) {
            throw new Exception('Could not add activity to DB' . $e->getMessage());
        }

        return $id;
    }

    public function getCommutesByUserId(UuidInterface $userId): array
    {
        // TODO: Implement getCommutesByUserId() method.
    }
}
