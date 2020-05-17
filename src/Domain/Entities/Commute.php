<?php

namespace CycleSaver\Domain\Entities;

use DateInterval;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

class Commute
{
    private ?UuidInterface $id;
    private ?UuidInterface $userId;
    private ?array $startLatLong;
    private ?array $endLatLong;
    private ?DateTimeImmutable $startDate;
    private ?DateInterval $activityDuration;
    private ?float $pTCost;
    private ?DateInterval $pTDuration;

    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id;
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function setId(?UuidInterface $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUserId(): ?UuidInterface
    {
        return $this->userId;
    }

    public function setUserId(?UuidInterface $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getStartLatLong(): ?array
    {
        return $this->startLatLong;
    }

    public function setStartLatLong(?array $startLatLong): self
    {
        $this->startLatLong = $startLatLong;
        return $this;
    }

    public function getEndLatLong(): ?array
    {
        return $this->endLatLong;
    }

    public function setEndLatLong(?array $endLatLong): self
    {
        $this->endLatLong = $endLatLong;
        return $this;
    }

    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getActivityDuration(): ?DateInterval
    {
        return $this->activityDuration;
    }

    public function setActivityDuration(?DateInterval $activityDuration): self
    {
        $this->activityDuration = $activityDuration;
        return $this;
    }

    public function getPTCost(): ?float
    {
        return $this->pTCost;
    }

    public function setPTCost(?float $pTCost): self
    {
        $this->pTCost = $pTCost;
        return $this;
    }

    public function getPTDuration(): ?DateInterval
    {
        return $this->pTDuration;
    }

    public function setPTDuration(?DateInterval $pTDuration): self
    {
        $this->pTDuration = $pTDuration;
        return $this;
    }

    public static function createFromActivityAndPTJourney(Activity $activity, PTJourney $journey): Commute
    {
        return (new Commute())
            ->setStartDate($activity->getStartDate())
            ->setStartLatLong($activity->getStartLatLong())
            ->setEndLatLong($activity->getEndLatLong())
            ->setActivityDuration($activity->getDuration())
            ->setPTDuration($journey->getDuration())
            ->setPTCost($journey->getCost());
    }
}
