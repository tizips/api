<?php

declare(strict_types=1);

use Hyperf\HttpServer\Router\Router;

Router::addGroup('/open', function () {

    Router::addGroup('/categories', function () {
        Router::get('', [App\Controller\Open\CategoryController::class, 'toTree']);
        Router::get('/{uri}', [App\Controller\Open\CategoryController::class, 'toInformation']);
    });

    Router::addGroup('/articles', function () {
        Router::get('', [App\Controller\Open\ArticleController::class, 'toPaginate']);
        Router::get('/{id}', [App\Controller\Open\ArticleController::class, 'toInformation']);
    });

    Router::addGroup('/links', function () {
        Router::get('', [App\Controller\Open\LinkController::class, 'toList']);
    });

    Router::addGroup('/system', function () {
        Router::get('/site', [App\Controller\Open\SystemController::class, 'toSite']);
    });
});

Router::addGroup('/admin', function () {

    Router::addGroup('/login', function () {
        Router::post('/account', [App\Controller\Admin\LoginController::class, 'doLogin']);
    });

    Router::addGroup('', function () {

        Router::get('/apis', [App\Controller\Admin\HelperController::class, 'toApis']);
        Router::post('/upload', [App\Controller\Admin\HelperController::class, 'doUpload']);

        Router::addGroup('/account', function () {
            Router::get('', [App\Controller\Admin\AccountController::class, 'toAccount']);
            Router::get('/permission', [App\Controller\Admin\AccountController::class, 'toPermission']);
            Router::put('', [App\Controller\Admin\AccountController::class, 'doUpdate']);
            Router::post('/logout', [App\Controller\Admin\AccountController::class, 'doLogout']);
        });

        Router::addGroup('/permission', function () {
            Router::addGroup('s', function () {
                Router::get('', [App\Controller\Admin\PermissionController::class, 'toTree']);
                Router::put('/{id}', [App\Controller\Admin\PermissionController::class, 'doUpdate']);
                Router::delete('/{id}', [App\Controller\Admin\PermissionController::class, 'doDelete']);
            });
            Router::get('/self', [App\Controller\Admin\PermissionController::class, 'toSelf']);
            Router::get('/parents', [App\Controller\Admin\PermissionController::class, 'toParents']);
            Router::post('', [App\Controller\Admin\PermissionController::class, 'doCreate']);
        });

        Router::addGroup('/role', function () {
            Router::addGroup('s', function () {
                Router::get('', [App\Controller\Admin\RoleController::class, 'toPaginate']);
                Router::put('/{id}', [App\Controller\Admin\RoleController::class, 'doUpdate']);
                Router::delete('/{id}', [App\Controller\Admin\RoleController::class, 'doDelete']);
            });
            Router::get('/self', [App\Controller\Admin\RoleController::class, 'toSelf']);
            Router::post('', [App\Controller\Admin\RoleController::class, 'doCreate']);
        });

        Router::addGroup('/admin', function () {
            Router::addGroup('s', function () {
                Router::get('', [App\Controller\Admin\AdminController::class, 'toPaginate']);
                Router::put('/{id}', [App\Controller\Admin\AdminController::class, 'doUpdate']);
                Router::delete('/{id}', [App\Controller\Admin\AdminController::class, 'doDelete']);
            });
            Router::post('', [App\Controller\Admin\AdminController::class, 'doCreate']);
            Router::put('/enable', [App\Controller\Admin\AdminController::class, 'doEnable']);
        });

        Router::addGroup('/category', function () {
            Router::post('', [App\Controller\Admin\CategoryController::class, 'doCreate']);
            Router::get('/parents', [App\Controller\Admin\CategoryController::class, 'toParents']);
            Router::get('/used', [App\Controller\Admin\CategoryController::class, 'toUsed']);
            Router::put('/enable', [App\Controller\Admin\CategoryController::class, 'doEnable']);
        });

        Router::addGroup('/categories', function () {
            Router::get('', [App\Controller\Admin\CategoryController::class, 'toTree']);
            Router::get('/{id}', [App\Controller\Admin\CategoryController::class, 'toInformation']);
            Router::put('/{id}', [App\Controller\Admin\CategoryController::class, 'doUpdate']);
            Router::delete('/{id}', [App\Controller\Admin\CategoryController::class, 'doDelete']);
        });

        Router::addGroup('/article', function () {
            Router::addGroup('s', function () {
                Router::get('', [App\Controller\Admin\ArticleController::class, 'toPaginate']);
                Router::put('/{id}', [App\Controller\Admin\ArticleController::class, 'doUpdate']);
                Router::get('/{id}', [App\Controller\Admin\ArticleController::class, 'toInformation']);
                Router::delete('/{id}', [App\Controller\Admin\ArticleController::class, 'doDelete']);
            });

            Router::put('/enable', [App\Controller\Admin\ArticleController::class, 'doEnable']);
            Router::post('', [App\Controller\Admin\ArticleController::class, 'doCreate']);
        });

        Router::addGroup('/link', function () {
            Router::addGroup('s', function () {
                Router::get('', [App\Controller\Admin\LinkController::class, 'toPaginate']);
                Router::put('/{id}', [App\Controller\Admin\LinkController::class, 'doUpdate']);
                Router::delete('/{id}', [App\Controller\Admin\LinkController::class, 'doDelete']);
            });

            Router::put('/enable', [App\Controller\Admin\LinkController::class, 'doEnable']);
            Router::post('', [App\Controller\Admin\LinkController::class, 'doCreate']);
        });

        Router::addGroup('/system', function () {
            Router::get('', [App\Controller\Admin\SystemController::class, 'toList']);
            Router::put('', [App\Controller\Admin\SystemController::class, 'doUpdate']);
        });

    }, [
        'middleware' => [
            App\Middleware\Auth\AuthMiddleware::class,
            App\Middleware\Auth\CasbinMiddleware::class,
        ]
    ]);

}, [
    'middleware' => [
        App\Middleware\Auth\ParseMiddleware::class,
    ],
]);