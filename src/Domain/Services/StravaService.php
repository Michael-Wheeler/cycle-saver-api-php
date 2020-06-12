<?php

namespace CycleSaver\Domain\Services;

use CycleSaver\Domain\Entities\Activity;
use CycleSaver\Domain\Entities\Commute;
use CycleSaver\Domain\Entities\User;
use CycleSaver\Domain\Repository\CommuteRepositoryInterface;
use CycleSaver\Domain\Repository\RepositoryException;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use CycleSaver\Infrastructure\Strava\StravaRepository;
use CycleSaver\Infrastructure\Tfl\TflApiRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class StravaService
{
    private StravaRepository $stravaRepo;
    private UserRepositoryInterface $userRepo;
    private CommuteRepositoryInterface $commuteRepo;
    private TflApiRepository $tflRepo;

    public function __construct(
        StravaRepository $stravaRepo,
        UserRepositoryInterface $userRepo,
        CommuteRepositoryInterface $commuteRepo,
        TflApiRepository $tflRepo
    ) {
        $this->stravaRepo = $stravaRepo;
        $this->userRepo = $userRepo;
        $this->commuteRepo = $commuteRepo;
        $this->tflRepo = $tflRepo;
    }

    /**
     * @param string $authCode
     * @return UuidInterface|null
     * @throws RepositoryException
     */
    public function createStravaUser(string $authCode)
    {
        $user = $this->createUser($authCode);

        $activities = $this->stravaRepo->getActivities($user);

        $journeys = $this->calculateJourneys($activities);

        foreach ($activities as $index => $activity) {
            if (!$activity || !$journeys[$index]) {
                continue;
            }

            $commute = Commute::createFromActivityAndPTJourney($activity, $journeys[$index]);

            $this->commuteRepo->saveCommute($commute->setUserId($user->getId()));
        }

        return $user->getId();
    }

    /**
     * @param string $authCode
     * @return User
     * @throws RepositoryException
     */
    private function createUser(string $authCode): User
    {
        $user = new User($userId = Uuid::uuid4());

        $this->userRepo->save($user);

        $this->stravaRepo->createUser($userId, $authCode);

        return $user;
    }

    /**
     * @param array $activities
     * @return array
     */
    private function calculateJourneys(array $activities): array
    {
        $activitiesCoordinates = array_map(function (Activity $activity) {
            return [
                'startLatLng' => $activity->getStartLatLong(),
                'endLatLng' => $activity->getEndLatLong()
            ];
        }, $activities);

        return $this->tflRepo->getPTJourneys($activitiesCoordinates);
    }
}
