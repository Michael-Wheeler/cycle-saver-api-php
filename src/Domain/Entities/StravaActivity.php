<?php

namespace CycleSaver\Domain\Entities;

use DateInterval;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

class StravaActivity extends Activity
{
    private ?int $distance;

    public function __construct(
        array $startLatLong,
        array $endLatLong,
        DateTimeImmutable $startDate,
        DateInterval $duration,
        int $distance,
        UuidInterface $id = null,
        UuidInterface $userId = null
    ) {
        parent::__construct(
            $startLatLong,
            $endLatLong,
            $startDate,
            $duration,
            $id,
            $userId
        );

        $this->distance = $distance;
    }

    public function getDistance(): int
    {
        return $this->distance;
    }
}
