<?php

namespace CycleSaver\Test;

use CycleSaver\Application\Bootstrap\ContainerFactory;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

abstract class IntegrationTestCase extends TestCase
{
    protected ContainerInterface $container;
    protected Manager $DBManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = (new ContainerFactory())->create();
        $this->DBManager = $this->container->get(Manager::class);

        $bulk = new BulkWrite();
        $bulk->delete([]);
        $this->DBManager->executeBulkWrite('cyclesaver.users', $bulk);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
