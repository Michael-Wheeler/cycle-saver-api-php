<?php

namespace CycleSaver\Domain\Services;

class JobService
{
    public function createStravaUserJob(string $authCode)
    {
        $this->stravaService->createStravaUser($authCode);

        return $this->jobRepository->createJob();
    }
}
