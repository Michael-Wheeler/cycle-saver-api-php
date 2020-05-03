<?php

use CycleSaver\Application\Handlers\HttpErrorHandler;
use CycleSaver\Application\Handlers\ShutdownHandler;

$displayErrorDetails = true;

$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);

register_shutdown_function($shutdownHandler);
