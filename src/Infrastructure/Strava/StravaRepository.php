<?php

namespace CycleSaver\Infrastructure\Strava;

use CycleSaver\Domain\Entities\Activity;
use CycleSaver\Domain\Entities\StravaUser;
use CycleSaver\Domain\Entities\User;
use CycleSaver\Domain\Repository\ActivityRepositoryInterface;
use CycleSaver\Domain\Repository\RepositoryException;
use CycleSaver\Infrastructure\Strava\Client\StravaApiAuthClient;
use CycleSaver\Infrastructure\Strava\Client\StravaApiClient;
use CycleSaver\Infrastructure\Strava\Exception\StravaAuthClientException;
use CycleSaver\Infrastructure\Strava\Exception\StravaClientException;
use InvalidArgumentException;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\Driver\Exception\RuntimeException as DriverRuntimeException;
use MongoDB\Exception\UnsupportedException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class StravaRepository implements ActivityRepositoryInterface
{
    private string $collectionName = 'strava';

    private StravaApiAuthClient $authClient;
    private StravaApiClient $client;
    private Collection $collection;
    private LoggerInterface $logger;

    public function __construct(
        StravaApiAuthClient $authClient,
        StravaApiClient $client,
        Database $database,
        LoggerInterface $logger
    ) {
        $this->authClient = $authClient;
        $this->client = $client;
        $this->collection = $database->selectCollection($this->collectionName);
        $this->logger = $logger;
    }

    /**
     * Swap authorisation code for access token and refresh token
     *
     * @param UuidInterface $userId
     * @param string $authCode
     * @return void
     * @throws RepositoryException
     */
    public function createUser(UuidInterface $userId, string $authCode): void
    {
        try {
            [$athleteID, $refreshToken] = $this->authClient->authenticateUser($authCode);
        } catch (StravaAuthClientException $e) {
            throw new RepositoryException('Unable to authenticate Strava user');
        }

        $user = new StravaUser(null, $userId, $athleteID, $refreshToken);

        $this->saveUser($user);
    }

    /**
     * @param User $user
     * @return Activity[]
     * @throws RepositoryException
     */
    public function getActivities(User $user): array
    {
        $stravaUser = $this->getStravaUserByUserId($user->getId());

        try {
            [$accessToken, $refreshToken] = $this->authClient->getAccessToken($stravaUser->getRefreshToken());
        } catch (StravaAuthClientException $e) {
            throw new RepositoryException("Unable to authenticate user '{$user->getId()}' in Strava");
        }

        $stravaUser->setRefreshToken($refreshToken);

        $this->updateUser($stravaUser);

        try {
            return $this->client->getActivities($accessToken);
        } catch (StravaClientException $e) {
            throw new RepositoryException("Unable to retrieve Strava activities for user {$user->getId()}");
        }
    }

    public function saveUser(StravaUser $user): void
    {
        $userArray = [
            '_id' => (string) $user->getId(),
            'user_id' => (string) $user->getUserId(),
            'strava_id' => $user->getStravaId(),
            'refresh_token' => $user->getRefreshToken(),
        ];

        try {
            $this->collection->insertOne($userArray);
        } catch (InvalidArgumentException | DriverRuntimeException $e) {
            throw new InvalidArgumentException('Could not add Strava user to collection: ' . $e->getMessage());
        }
    }

    public function updateUser(StravaUser $user): void
    {
        try {
            $this->collection->updateOne(
                ['_id' => (string) $user->getId()],
                [
                    '$set' => [
                        'user_id' => (string) $user->getUserId(),
                        'strava_id' => (string) $user->getStravaId(),
                        'refresh_token' => $user->getRefreshToken(),
                    ]
                ]
            );
        } catch (UnsupportedException | \MongoDB\Exception\InvalidArgumentException | DriverRuntimeException $e) {
            throw new InvalidArgumentException('Error when updating Strava user: ' . $e->getMessage());
        }
    }

    /**
     * @param UuidInterface $userId
     * @return StravaUser
     * @throws InvalidArgumentException
     */
    public function getStravaUserByUserId(UuidInterface $userId): StravaUser
    {
        try {
            $document = $this->collection->findOne(['user_id' => (string) $userId]);

            return $this->documentToStravaUser($document);
        } catch (UnsupportedException | \MongoDB\Exception\InvalidArgumentException | DriverRuntimeException $e) {
            throw new InvalidArgumentException('Error when retrieving Strava user: ' . $e->getMessage());
        }
    }

    private function documentToStravaUser(object $document): StravaUser
    {
        return new StravaUser(
            Uuid::fromString($document->_id),
            Uuid::fromString($document->user_id),
            $document->strava_id,
            $document->refresh_token
        );
    }
}
