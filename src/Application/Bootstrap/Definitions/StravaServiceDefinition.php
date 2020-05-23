<?php

namespace CycleSaver\Application\Bootstrap\Definitions;

use CycleSaver\Application\Bootstrap\ContainerException;
use CycleSaver\Domain\Repository\CommuteRepositoryInterface;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use CycleSaver\Domain\Services\StravaService;
use CycleSaver\Infrastructure\Strava\Client\StravaApiAuthClient;
use CycleSaver\Infrastructure\Strava\Client\StravaApiClient;
use CycleSaver\Infrastructure\Strava\Client\StravaContext;
use CycleSaver\Infrastructure\Strava\StravaRepository;
use CycleSaver\Infrastructure\Tfl\Client\TflApiClient;
use CycleSaver\Infrastructure\Tfl\Client\TflContext;
use CycleSaver\Infrastructure\Tfl\TflRepository;
use DI\Container;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

class StravaServiceDefinition implements ServiceDefinition
{
    public static function getDefinitions(): array
    {
        return [
            StravaService::class => function (Container $c) {
                $authClient = new StravaApiAuthClient(
                    $c->get(StravaContext::class),
                    $c->get(ClientInterface::class),
                    $c->get(LoggerInterface::class)
                );

                $client = new StravaApiClient(
                    $c->get(StravaContext::class),
                    $c->get(ClientInterface::class),
                    $authClient,
                    $c->get(LoggerInterface::class)
                );

                $stravaRepo = new StravaRepository(
                    $authClient,
                    $client
                );

                $tflRepo = new TflRepository(
                    new TflApiClient(
                        $c->get(TflContext::class),
                        $c->get(ClientInterface::class),
                        $c->get(LoggerInterface::class),
                        $c->get(\DateTimeInterface::class)
                    )
                );

                $userRepo = $c->get(UserRepositoryInterface::class);
                $commuteRepo = $c->get(CommuteRepositoryInterface::class);

                if (!$stravaRepo || !$userRepo || !$commuteRepo) {
                    throw new ContainerException('Unable to retrieve Strava service dependencies');
                }

                return new StravaService($stravaRepo, $userRepo, $commuteRepo, $tflRepo);
            }
        ];
    }
}
