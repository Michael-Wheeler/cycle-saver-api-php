<?php

use CycleSaver\Application\Bootstrap\ContainerFactory;
use Dotenv\Dotenv;

//require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/.');
$vars = $dotenv->load();

return (new ContainerFactory())->create();

//return (new \Opia\Note\Bootstrap\ContainerFactory)->create(
//    \Opia\Note\Bootstrap\ConfigFactory::create()
//);
