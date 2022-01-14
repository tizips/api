<?php

declare(strict_types=1);

if (! function_exists('path_base')) {
    function path_base(string $dir = ''): string
    {
        return BASE_PATH . $dir;
    }
}

if (! function_exists('routes')) {
    function routes()
    {
        $path = path_base('/routes');

        $files = scandir($path);

        foreach ($files as $file) {
            if (Hyperf\Utils\Str::endsWith($file, 'php')) {
                require_once $path . '/' . $file;
            }
        }
    }
}