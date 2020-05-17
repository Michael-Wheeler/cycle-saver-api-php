<?php

namespace CycleSaver\Domain\Repository;

use CycleSaver\Domain\Entities\Commute;
use Ramsey\Uuid\UuidInterface;

interface CommuteRepositoryInterface
{
    /**
     * @return Commute[]
     */
    public function getCommutes(): array;

    public function saveCommute(Commute $commute): UuidInterface;

    /**
     * @param UuidInterface $userId
     * @return Commute[]
     */
    public function getCommutesByUserId(UuidInterface $userId): array;
}
