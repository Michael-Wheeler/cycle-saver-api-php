<?php

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../src/Application/Bootstrap/bootstrap.php';

require __DIR__ . '/../src/Application/HttpServer.php';

$app->run();
