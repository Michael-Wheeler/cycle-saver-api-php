<?php
//TODO what does this do?
$app->addRoutingMiddleware();

/** Examples of middleware running before and after controller. Uncomment to try and then remove */
//$app->add(new TestBeforeMiddleware());
//$app->add(new TestAfterMiddleware());

/**
 * Add Error Handling Middleware
 *
 * @param bool $displayErrorDetails -> Set in error handler, should be set to false in production
 * @param bool $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool $logErrorDetails -> Display error details in error log
 * which can be replaced by a callable of your choice.
 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
//TODO make display error details env variable
//TODO Decide if we want or need this middleware or if there is an alternative
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);
