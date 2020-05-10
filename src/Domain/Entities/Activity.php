<?php

namespace CycleSaver\Domain\Entities;

use DatePeriod;
use DateTimeImmutable;
use Ramsey\Uuid\Rfc4122\UuidInterface;

abstract class Activity
{
    private UuidInterface $id;

    private string $startLatLong;

    private string $endLatLong;

    private DateTimeImmutable $start;

    private DatePeriod $duration;
}
