<?php

declare(strict_types=1);

namespace App\Helper;


class Admin
{
    public static function admin(int $id): string
    {
        return sprintf('ADMIN:%d', $id);
    }
}