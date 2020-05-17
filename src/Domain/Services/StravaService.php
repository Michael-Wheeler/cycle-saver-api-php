<?php

namespace CycleSaver\Domain\Services;

use CycleSaver\Domain\Entities\Commute;
use CycleSaver\Domain\Entities\User;
use CycleSaver\Domain\Repository\CommuteRepositoryInterface;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use CycleSaver\Infrastructure\Strava\Exception\StravaAuthClientException;
use CycleSaver\Infrastructure\Strava\Exception\StravaClientException;
use CycleSaver\Infrastructure\Strava\StravaRepository;
use CycleSaver\Infrastructure\Tfl\Exception\TflClientException;
use CycleSaver\Infrastructure\Tfl\TflRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class StravaService
{
    private StravaRepository $stravaRepo;
    private UserRepositoryInterface $userRepo;
    private CommuteRepositoryInterface $commuteRepo;
    private TflRepository $tflRepo;

    public function __construct(
        StravaRepository $stravaRepo,
        UserRepositoryInterface $userRepo,
        CommuteRepositoryInterface $commuteRepo,
        TflRepository $tflRepo
    ) {
        $this->stravaRepo = $stravaRepo;
        $this->userRepo = $userRepo;
        $this->commuteRepo = $commuteRepo;
        $this->tflRepo = $tflRepo;
    }

    /**
     * @param string $authCode
     * @return UuidInterface|null
     * @throws StravaAuthClientException
     * @throws StravaClientException
     * @throws TflClientException
     */
    public function newUser(string $authCode)
    {
        $user = new User($userId = Uuid::uuid4());

        [$accessToken, $refreshToken] = $this->stravaRepo->authoriseUser($authCode);

        $this->userRepo->save($user->setRefreshToken($refreshToken));

        $activities = $this->stravaRepo->getActivities($accessToken);

        foreach ($activities as $activity) {
            $pTJourney = $this->tflRepo->getPTJourney(
                $activity->getStartLatLong(),
                $activity->getEndLatLong()
            );

            $commute = Commute::createFromActivityAndPTJourney($activity, $pTJourney);

            $this->commuteRepo->saveCommute($commute->setUserId($userId));
        }

        return $user->getId();
    }
}
