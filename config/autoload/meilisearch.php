<?php

declare(strict_types=1);

return [
    'url' => env('MEILISEARCH_URL', 'http://127.0.0.1'),
    'port' => env('MEILISEARCH_PORT', 7700),
    'key' => env('MEILISEARCH_KEY', null),
];