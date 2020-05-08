<?php

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/.');
$vars = $dotenv->load();


//return (new \Opia\Note\Bootstrap\ContainerFactory)->create(
//    \Opia\Note\Bootstrap\ConfigFactory::create()
//);
