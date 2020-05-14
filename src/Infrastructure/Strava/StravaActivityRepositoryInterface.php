<?php

namespace CycleSaver\Infrastructure;

use CycleSaver\Domain\Entities\Activity;
use CycleSaver\Domain\Repository\ActivityRepositoryInterface;

class StravaActivityRepositoryInterface implements ActivityRepositoryInterface
{
    public function getActivities(): array
    {
        // TODO: Implement getActivities() method.
    }
}
