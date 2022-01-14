<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/elasticsearch.
 *
 * @link     https://github.com/hyperf-ext/elasticsearch
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/elasticsearch/blob/master/LICENSE
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Elasticsearch Client Configuration
    |--------------------------------------------------------------------------
    |
    | This array will be passed to the Elasticsearch client.
    | See configuration options here:
    |
    | http://www.elasticsearch.org/guide/en/elasticsearch/client/php-api/current/_configuration.html
    */

    'client' => [
        'hosts' => [
            [
                'host' => env('ES_HOST', '127.0.0.1'),
                'port' => env('ES_POST', 9200),
                'scheme' => env('ES_SCHEME', 'http'),
                'user' => env('ES_USER'),
                'pass' => env('ES_PASS'),
            ]
        ],
        'retries' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch Logger Options
    |--------------------------------------------------------------------------
    |
    | The `hyperf/logger` component is required if enabled.
    */

    'logger' => [
        'enabled' => env('ES_LOGGER', false),
        'name' => 'elasticsearch',
        'group' => 'elasticsearch',
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Pool Options
    |--------------------------------------------------------------------------
    */

    'pool' => [
        'enabled' => env('ES_POOL', false),
        'min_connections' => 1,
        'max_connections' => 30,
        'wait_timeout' => 3.0,
        'max_idle_time' => 60.0,
    ],
];
