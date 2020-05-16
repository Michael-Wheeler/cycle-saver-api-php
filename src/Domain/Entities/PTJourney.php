<?php

namespace CycleSaver\Domain\Entities;

use DateInterval;

class PTJourney
{
    private float $cost;
    private DateInterval $duration;

    public function __construct(float $cost, DateInterval $duration)
    {
        $this->cost = $cost;
        $this->duration = $duration;
    }

    public function getCost(): float
    {
        return $this->cost;
    }

    public function getDuration(): DateInterval
    {
        return $this->duration;
    }
}
