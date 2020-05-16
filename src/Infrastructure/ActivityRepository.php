<?php

namespace CycleSaver\Infrastructure;

use CycleSaver\Domain\Entities\Activity;
use CycleSaver\Domain\Repository\ActivityRepositoryInterface;
use Exception;
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
     * @param Activity $activity
     * @return UuidInterface
     * @throws Exception
     */
    public function saveActivity(Activity $activity): UuidInterface
    {
        $id = $activity->getId() ?? Uuid::uuid4();

        $activityArray = [
            '_id' => (string) $id,
            'start_latlng' => $activity->getStartLatLong(),
            'end_latlng' => $activity->getEndLatLong(),
            'start_date' => $activity->getStartDate(),
            'duration' => $activity->getDuration(),
            'user_id' => $activity->getUserId() ?? Uuid::uuid4()
        ];

        $bulk = new BulkWrite();
        $bulk->insert($activityArray);

        try {
            $this->manager->executeBulkWrite($this->userNamespace, $bulk);
        } catch (Exception $e) {
            throw new Exception('Could not add activity to DB' . $e->getMessage());
        }

        return $id;
    }
}
