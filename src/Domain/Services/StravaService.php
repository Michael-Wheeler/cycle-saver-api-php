<?php

namespace CycleSaver\Domain\Services;

use CycleSaver\Domain\Entities\User;
use CycleSaver\Domain\Repository\ActivityRepositoryInterface;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use CycleSaver\Infrastructure\Strava\Exception\StravaAuthClientException;
use CycleSaver\Infrastructure\Strava\Exception\StravaClientException;
use CycleSaver\Infrastructure\Strava\StravaRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class StravaService
{
    private StravaRepository $stravaRepo;
    private UserRepositoryInterface $userRepo;
    private ActivityRepositoryInterface $activityRepo;

    public function __construct(
        StravaRepository $stravaRepo,
        UserRepositoryInterface $userRepo,
        ActivityRepositoryInterface $activityRepo
    ) {
        $this->stravaRepo = $stravaRepo;
        $this->userRepo = $userRepo;
        $this->activityRepo = $activityRepo;
    }

    /**
     * @param string $authCode
     * @return UuidInterface|null
     * @throws StravaAuthClientException
     * @throws StravaClientException
     */
    public function newUser(string $authCode)
    {
        $user = new User(Uuid::uuid4());

        [$accessToken, $refreshToken] = $this->stravaRepo->authoriseUser($authCode);

        $this->userRepo->save($user->setRefreshToken($refreshToken));

        $activities = $this->stravaRepo->getActivities($accessToken);

        foreach ($activities as $activity) {
            $this->activityRepo->saveActivity($activity->setUserId($id));
        }

        return $user->getId();
    }
}
