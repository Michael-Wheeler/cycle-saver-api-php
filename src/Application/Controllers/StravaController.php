<?php

namespace CycleSaver\Application\Controllers;

use CycleSaver\Application\ResponseFactory;
use CycleSaver\Domain\Services\StravaService;
use CycleSaver\Infrastructure\Strava\Exception\StravaAuthClientException;
use CycleSaver\Infrastructure\Strava\Exception\StravaClientException;
use CycleSaver\Infrastructure\Tfl\Exception\TflClientException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class StravaController
{
    private StravaService $stravaService;

    public function __construct(StravaService $stravaService)
    {
        $this->stravaService = $stravaService;
    }

    public function newUser(ServerRequestInterface $request, Response $response, $args): ResponseInterface
    {
        $authorisationCode = $request->getQueryParams()['code'] ?? null;

        if (!$authorisationCode) {
            throw new InvalidArgumentException('Strava authorisation code required.');
        }

        try {
            $newUserId = $this->stravaService->newUser($authorisationCode);
        } catch (StravaAuthClientException | StravaClientException | TflClientException $e) {
            return ResponseFactory::createInternalErrorResponse(
                'Unable to create new user from Strava: ' . $e->getMessage(),
                $response
            );
        }

        return ResponseFactory::createSuccessfulCreationResponse(
            (object) [
                'id' => (string) $newUserId
            ],
            $response
        );
    }
}
