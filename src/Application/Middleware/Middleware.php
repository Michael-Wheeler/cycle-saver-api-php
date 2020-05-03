<?php

use CycleSaver\Application\Middleware\TestAfterMiddleware;
use CycleSaver\Application\Middleware\TestBeforeMiddleware;

// Add Routing Middleware
//TODO what does this do?
$app->addRoutingMiddleware();

$app->add(new TestBeforeMiddleware());
$app->add(new TestAfterMiddleware());

/**
 * Add Error Handling Middleware
 *
 * @param bool $displayErrorDetails -> Should be set to false in production
 * @param bool $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool $logErrorDetails -> Display error details in error log
 * which can be replaced by a callable of your choice.
 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
//TODO make display error details env variable
//TODO Decide if we want or need this middleware or if there is an alternative
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);
$errorMiddleware->setDefaultErrorHandler($errorHandler);
