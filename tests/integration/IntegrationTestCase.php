<?php

namespace CycleSaver\Test;

use CycleSaver\Application\Bootstrap\ContainerFactory;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

abstract class IntegrationTestCase extends TestCase
{
    protected ContainerInterface $container;
    protected Database $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = (new ContainerFactory())->create();

        $this->database = $this->container->get(Database::class);

        $this->database->dropCollection('users');
        $this->database->dropCollection('commutes');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
