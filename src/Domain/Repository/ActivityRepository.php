<?php

namespace CycleSaver\Domain\Repository;

use CycleSaver\Domain\Entities\Activity;

interface ActivityRepository
{
    /**
     * @return Activity[]
     */
    public function getActivities(): array;
}
