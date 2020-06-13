<?php

use CycleSaver\Application\Controllers\CommuteController;
use CycleSaver\Application\Controllers\StravaController;

// TODO Disabled until user auth added.
//$app->post('/user', UserController::class . ':createUser');

$app->get('/user/{id}/commute', CommuteController::class . ':getByUserId');

$app->post('/strava/new-user', StravaController::class . ':newUser');
