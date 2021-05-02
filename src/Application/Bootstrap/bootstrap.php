<?php

use CycleSaver\Application\Bootstrap\ContainerFactory;
use Dotenv\Dotenv;

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
    $vars = $dotenv->load();
} catch (Exception $e) {
}

$container = (new ContainerFactory())->create();
