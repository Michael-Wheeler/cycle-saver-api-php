<?php

use CycleSaver\Application\Middleware\CorsMiddleware;

$app->addRoutingMiddleware();

$app->add(CorsMiddleware::class);

$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);
