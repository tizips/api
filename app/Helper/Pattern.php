<?php

declare(strict_types=1);

namespace App\Helper;

class Pattern
{
    const ROUTE = '/\|\s([a-zA-Z]+)\s+\|\s([a-zA-Z\|]+)\s+\|\s([\/a-zA-Z\{\}\-\_]+)+\s+\|\s([a-zA-z]+::[a-zA-Z]+)\s+\|/';

    const ADMIN_USERNAME = '/^[a-zA-Z0-9\-\_]{4,20}$/';
    const ADMIN_PASSWORD = '/^[a-zA-Z0-9\-\_\@\$\&\%\!]{4,20}$/';

    const FILE_DIR = '/^(\/[a-zA-Z0-9\-\_]{2,20}){1,3}$/';

    const URI = '/^(http(s)?\:\/\/([a-zA-Z\d\-]+\.)+[a-zA-Z]+)|([a-zA-Z\d\-\_]+)$/';

    const CONFIG_CATEGORY_CODE = '/^[a-zA-Z]([a-zA-Z0-9]*:?)+[a-zA-Z]$/';
    const CONFIG_KEY = '/^[a-zA-Z]([a-zA-Z0-9]*:?)+[a-zA-Z]$/';

    const PERMISSION_SLUG = '/^([a-z][a-z0-9]*(\.)?)*[a-z0-9]*[a-z]$/';
    const PERMISSION_PATH = '/^[a-zA-Z0-9\/\{\}]+$/';

    const MOBILE = '/^1[2-9][0-9]{9}$/';

    const NO_IDCARD = '/^[1-9]\d{5}(18|19|20)\d{2}((0[1-9])|(1[0-2]))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/';
}