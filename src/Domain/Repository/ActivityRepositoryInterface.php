<?php

namespace CycleSaver\Domain\Repository;

use CycleSaver\Domain\Entities\Activity;
use Ramsey\Uuid\UuidInterface;

interface ActivityRepositoryInterface
{
    /**
     * @return Activity[]
     */
    public function getActivities(): array;

    public function saveActivity(Activity $activity): UuidInterface;
}
