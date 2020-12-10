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

Router::get('/init', function () {
    return [
        'username' => 'admin',
        'password' => '123456',
        'password_encryption' => password_hash('123456', PASSWORD_BCRYPT),
    ];
});

Router::get('/', [App\Controller\IndexController::class, 'index']);

Router::post('/auth', [App\Controller\Auth\AuthController::class, 'doJwtToken']);

Router::addGroup('', function () {

    Router::addGroup('/admin', function () {
        Router::get('', [App\Controller\Admin\AdminController::class, 'doAdminInformation']);
    });
}, [
    'middleware' => [
        App\Middleware\AuthApiMiddleware::class,
    ],
]);


