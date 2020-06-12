<?php

namespace CycleSaver\Domain\Repository;

use CycleSaver\Domain\Entities\Activity;
use CycleSaver\Domain\Entities\User;
use Ramsey\Uuid\UuidInterface;

interface ActivityRepositoryInterface
{
    /**
     * @param UuidInterface $userId
     *
     * Supplier specific registration auth requirements e.g. auth code
     * @param string $authDetails
     */
    public function createUser(UuidInterface $userId, string $authDetails): void;

    /**
     * @param User $user
     * @return Activity[]
     */
    public function getActivities(User $user): array;
}
