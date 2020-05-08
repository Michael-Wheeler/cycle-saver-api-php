<?php

use CycleSaver\Application\Bootstrap\ContainerFactory;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$vars = $dotenv->load();

$container = (new ContainerFactory())->create();
