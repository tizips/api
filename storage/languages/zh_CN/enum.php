<?php

declare(strict_types=1);

return [
    'validation' => [
        'enum' => ':attribute 不是有效的枚举实例',
        'enum_value' => ':attribute 不是有效的值',
        'enum_key' => ':attribute 不是有效的键',
    ],
    App\Enum\System\TypeEnum::class => [
        App\Enum\System\TypeEnum::SITE => '站点',
    ],
];
