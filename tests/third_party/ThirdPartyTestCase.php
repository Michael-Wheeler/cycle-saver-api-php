<?php

namespace CycleSaver;

use CycleSaver\Application\Bootstrap\ContainerFactory;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

abstract class ThirdPartyTestCase extends TestCase
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
}
