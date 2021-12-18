<?php

declare(strict_types=1);

if (! function_exists('path_base')) {
    function path_base(string $dir = ''): string
    {
        return BASE_PATH . $dir;
    }
}
