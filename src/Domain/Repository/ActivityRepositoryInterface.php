<?php

namespace CycleSaver\Domain\Repository;

use CycleSaver\Domain\Entities\Activity;

interface ActivityRepositoryInterface
{
    /**
     * @return Activity[]
     */
    public function getActivities(): array;
}
