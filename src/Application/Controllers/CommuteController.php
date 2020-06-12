<?php

namespace CycleSaver\Application\Controllers;

use CycleSaver\Application\ResponseFactory;
use CycleSaver\Domain\Entities\Commute;
use CycleSaver\Domain\Repository\CommuteRepositoryInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Slim\Psr7\Response;

class CommuteController
{
    private CommuteRepositoryInterface $repository;

    public function __construct(CommuteRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getByUserId(ServerRequestInterface $request, Response $response, $args): ResponseInterface
    {
        $userId = $args['id'] ?? null;
        $userId = Uuid::fromString($userId);

        if (!$userId) {
            throw new InvalidArgumentException('Valid user ID required to retrieve commutes');
        }

        $commutes = array_map(
            fn(Commute $commute) => $this->commuteToObject($commute),
            $this->repository->getCommutesByUserId($userId)
        );

        return ResponseFactory::createSuccessResponse(
            (object) ['commutes' => $commutes],
            $response
        );
    }

    private function commuteToObject(Commute $commute): object
    {
        return (object) [
            'id' => $commute->getId() ?? null,
            'user_id' => $commute->getUserId() ?? null,
            'start_date' => $commute->getStartDate()->getTimestamp() ?? null,
            'start_latlng' => $commute->getStartLatLong() ? implode(',', $commute->getStartLatLong()) : null,
            'end_latlng' => $commute->getEndLatLong() ? implode(',', $commute->getEndLatLong()) : null,
            'activity_duration' => $commute->getActivityDuration()->s ?? null,
            'public_transport_duration' => $commute->getPTDuration()->s ?? null,
            'public_transport_cost' => $commute->getPTCost() * 100
        ];
    }
}
