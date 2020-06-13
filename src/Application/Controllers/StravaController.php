<?php

namespace CycleSaver\Application\Controllers;

use CycleSaver\Application\ResponseFactory;
use CycleSaver\Domain\Repository\RepositoryException;
use CycleSaver\Domain\Services\StravaService;
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

    public function newUser(ServerRequestInterface $request, Response $response): ResponseInterface
    {
        $resetSeconds = ini_get('max_execution_time');
        set_time_limit(360);

        $authorisationCode = $request->getQueryParams()['code'] ?? null;

        if ($authorisationCode === null) {
            return ResponseFactory::createBadRequestResponse(
                'Strava auth code required to connect to Strava.',
                $response
            );
        }

        try {
            $newUserId = $this->stravaService->createStravaUser($authorisationCode);
        } catch (RepositoryException $e) {
            return ResponseFactory::createInternalErrorResponse(
                'Internal error occurred when creating new user from Strava data.',
                $response
            );
        }

        set_time_limit($resetSeconds);
        return ResponseFactory::createSuccessfulCreationResponse(
            (object) ['id' => (string) $newUserId],
            $response
        );
    }
}
