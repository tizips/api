<?php

declare(strict_types=1);

use Hyperf\HttpServer\Router\Router;

Router::addGroup('/open', function () {

    Router::addGroup('/categories', function () {
        Router::get('', [App\Controller\Open\CategoryController::class, 'toTree']);
        Router::get('/{uri}', [App\Controller\Open\CategoryController::class, 'toInformation']);
    });

    Router::addGroup('/article', function () {

        Router::addGroup('s', function () {
            Router::get('', [App\Controller\Open\ArticleController::class, 'toPaginate']);
            Router::get('/{id}', [App\Controller\Open\ArticleController::class, 'toInformation']);
        });

        Router::get('/search', [App\Controller\Open\ArticleController::class, 'toSearch']);
    });

    Router::addGroup('/links', function () {
        Router::get('', [App\Controller\Open\LinkController::class, 'toList']);
    });

    Router::addGroup('/system', function () {
        Router::get('/site', [App\Controller\Open\SystemController::class, 'toSite']);
    });
}, [
    'middleware' => [
        App\Middleware\Util\CorsMiddleware::class,
    ],
]);