<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\HttpServer\Router\Router;

Router::addGroup('/', function () {
    Router::get('', [App\Controller\Basic\IndexController::class, 'index']);
    Router::get('init', [App\Controller\Basic\IndexController::class, 'init']);
});

Router::addGroup('/authentication', function () {
    Router::post('/account', [App\Controller\Authentication\AuthenticationController::class, 'doAccount']);
});

Router::addGroup('', function () {
    Router::addGroup('/account', function () {
        Router::get('', [App\Controller\Account\AccountController::class, 'doInformation']);
    });
}, [
    'middleware' => [
        App\Middleware\AuthenticationMiddleware::class,
    ],
]);


