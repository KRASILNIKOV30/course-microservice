<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$isProduction = getenv('APP_ENV') === 'prod';

$app = AppFactory::create();

// Регистрация middlewares фреймворка Slim.
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(!$isProduction, true, true);

$app->post('/course', \App\Controller\CourseApiController::class . ':saveCourse');
$app->post('/enrollment', \App\Controller\CourseApiController::class . ':saveEnrollment');
$app->delete('/course/delete', \App\Controller\CourseApiController::class . ':deleteCourse');
$app->post('/course/module', \App\Controller\CourseApiController::class . ':saveModuleStatus');
$app->get('/course', \App\Controller\CourseApiController::class . ':getCourseStatus');
$app->get('/module', \App\Controller\CourseApiController::class . ':getModule');

$app->run();
