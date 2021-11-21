<?php

declare(strict_types=1);

namespace App\Helper;

class Casbin
{
    const ROOT = 'root';

    const DEFAULT = [
        [
            'method' => Method::POST,
            'path' => '/admin/login/account',
        ],
        [
            'method' => Method::GET,
            'path' => '/admin/account',
        ],
        [
            'method' => Method::POST,
            'path' => '/admin/account/logout',
        ],
        [
            'method' => Method::PUT,
            'path' => '/admin/account',
        ],
        [
            'method' => Method::POST,
            'path' => '/admin/upload',
        ],
        [
            'method' => Method::GET,
            'path' => '/admin/account/permission',
        ],
        [
            'method' => Method::GET,
            'path' => '/admin/apis',
        ],
        [
            'method' => Method::GET,
            'path' => '/admin/permission/self',
        ],
        [
            'method' => Method::GET,
            'path' => '/admin/permission/parents',
        ],
        [
            'method' => Method::GET,
            'path' => '/admin/role/self',
        ],
        [
            'method' => Method::GET,
            'path' => '/admin/category/parents',
        ],
        [
            'method' => Method::GET,
            'path' => '/admin/category/used',
        ],
        [
            'method' => Method::GET,
            'path' => '/admin/categories/{id}',
        ],
        [
            'method' => Method::GET,
            'path' => '/admin/articles/{id}',
        ],
    ];

    public static function user(int $id): string
    {
        return sprintf('admin:%d', $id);
    }

    public static function role(int $id): string
    {
        return sprintf('role:%d', $id);
    }
}