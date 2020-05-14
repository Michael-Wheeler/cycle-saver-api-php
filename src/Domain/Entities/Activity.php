<?php

namespace CycleSaver\Domain\Entities;

use DateInterval;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

abstract class Activity
{
    private ?UuidInterface $id;

    private ?UuidInterface $userId;

    private array $startLatLong;

    private array $endLatLong;

    private DateTimeImmutable $startDate;

    private DateInterval $duration;

    public function __construct(
        array $startLatLong,
        array $endLatLong,
        DateTimeImmutable $startDate,
        DateInterval $duration,
        UuidInterface $id = null,
        UuidInterface $userId = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->startLatLong = $startLatLong;
        $this->endLatLong = $endLatLong;
        $this->startDate = $startDate;
        $this->duration = $duration;
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getUserId(): ?UuidInterface
    {
        return $this->getUserId();
    }

    public function getStartLatLong(): array
    {
        return $this->startLatLong;
    }

    public function getEndLatLong(): array
    {
        return $this->endLatLong;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getDuration(): DateInterval
    {
        return $this->duration;
    }
}
