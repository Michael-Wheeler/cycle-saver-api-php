<?php

namespace CycleSaver\Infrastructure;

use CycleSaver\Domain\Entities\Commute;
use CycleSaver\Domain\Repository\CommuteRepositoryInterface;
use DateInterval;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\Driver\Exception\RuntimeException as DriverRuntimeException;
use MongoDB\Exception\UnsupportedException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CommuteRepository implements CommuteRepositoryInterface
{
    private string $collectionName = 'commutes';
    private Collection $collection;
    private LoggerInterface $logger;

    public function __construct(Database $database, LoggerInterface $logger)
    {
        $this->collection = $database->selectCollection($this->collectionName);
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
            'user_id' => (string) $commute->getUserId(),
            'start_latlng' => implode(',', $commute->getStartLatLong()),
            'end_latlng' => implode(',', $commute->getEndLatLong()),
            'start_date' => $commute->getStartDate()->getTimestamp(),
            'activity_duration' => $commute->getActivityDuration()->s,
            'public_transport_duration' => $commute->getPTDuration()->s,
            'public_transport_cost' => $commute->getPTCost(),
        ];

        try {
            $this->collection->insertOne($commuteArray);
        } catch (InvalidArgumentException | DriverRuntimeException $e) {
            throw new InvalidArgumentException('Could not add commute to collection: ' . $e->getMessage());
        }

        return $id;
    }

    public function deleteCommutesByUserId(UuidInterface $userId): void
    {
        try {
            $this->collection->deleteMany(['user_id' => (string) $userId]);
        } catch (UnsupportedException | \MongoDB\Exception\InvalidArgumentException | DriverRuntimeException $e) {
            throw new InvalidArgumentException('Error when deleting user commutes: ' . $e->getMessage());
        }
    }

    public function getCommutesByUserId(UuidInterface $userId): array
    {
        try {
            return array_map(
                fn(object $commuteDocument) => $this->documentToCommute($commuteDocument),
                $this->collection->find(['user_id' => (string) $userId])->toArray()
            );
        } catch (UnsupportedException | \MongoDB\Exception\InvalidArgumentException | DriverRuntimeException $e) {
            throw new InvalidArgumentException('Error when retrieving user commutes: ' . $e->getMessage());
        }
    }

    public function documentToCommute(object $document): Commute
    {
        $userId = $document->user_id ? Uuid::fromString($document->user_id) : null;
        $startLatLng = $document->start_latlng ? explode(',', $document->start_latlng) : null;
        $endLatLng = $document->end_latlng ? explode(',', $document->end_latlng) : null;

        $startDate = $document->start_date
            ? (new DateTimeImmutable())->setTimestamp($document->start_date)
            : null;

        $activityDuration = $document->activity_duration
            ? new DateInterval("PT{$document->activity_duration}S")
            : null;

        $pTDuration = $document->public_transport_duration
            ? new DateInterval("PT{$document->public_transport_duration}S")
            : null;

        return (new Commute())
            ->setId(Uuid::fromString($document->_id))
            ->setUserId($userId)
            ->setStartDate($startDate)
            ->setStartLatLong($startLatLng)
            ->setEndLatLong($endLatLng)
            ->setActivityDuration($activityDuration)
            ->setPTDuration($pTDuration)
            ->setPTCost($document->public_transport_cost ?? null);
    }
}
