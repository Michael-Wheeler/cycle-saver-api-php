<?php

namespace CycleSaver;

use CycleSaver\Application\Bootstrap\ContainerFactory;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

abstract class IntegrationTestCase extends TestCase
{
    protected ContainerInterface $container;
    protected Database $database;
    protected App $httpServer;

    protected function setUp(): void
    {
        parent::setUp();
        $container = (new ContainerFactory())->create();
        $this->container = $container;

        $this->httpServer = require __DIR__ . '/../../src/Application/HttpServer.php';

        $this->database = $this->container->get(Database::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->database->dropCollection('users');
        $this->database->dropCollection('commutes');
    }

    protected function makeRequest(ServerRequestInterface $uri): ResponseInterface
    {
        return $this->httpServer->handle($uri);
    }

    protected function getResponseBody(ResponseInterface $response): object
    {
        return json_decode((string) $response->getBody());
    }
}
