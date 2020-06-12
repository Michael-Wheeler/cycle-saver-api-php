<?php

namespace CycleSaver\Test;

use CycleSaver\Application\Bootstrap\ContainerFactory;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Slim\Psr7\Environment;
use Slim\Psr7\Factory\ResponseFactory;

abstract class IntegrationTestCase extends TestCase
{
    protected ContainerInterface $container;
    protected Database $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = (new ContainerFactory())->create();

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
        $container = $this->container;
        $app = require __DIR__ . '/../../src/Application/HttpServer.php';

        return $app->handle($uri);
    }
}
