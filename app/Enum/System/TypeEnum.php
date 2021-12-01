<?php

declare(strict_types=1);

namespace App\Enum\System;

use HyperfExt\Enum\Contracts\LocalizedEnum;
use HyperfExt\Enum\Enum;

final class TypeEnum extends Enum implements LocalizedEnum
{
    const SITE = 'site';
}
