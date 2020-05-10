<?php

namespace CycleSaver\Domain\Entities;

use DatePeriod;

abstract class PublicTransportJourney
{
    private float $cost;

    private DatePeriod $duration;
}
