<?php

declare(strict_types=1);

namespace App\Helper;

use App\Enum\System\TypeEnum;
use App\Model\System;

class Admin
{
    public static function admin(int $id): string
    {
        return sprintf('ADMIN:%d', $id);
    }

    const KEYS = [
        TypeEnum::SITE => [
            [
                'label' => '名称',
                'key' => 'name',
                'genre' => System::GENRE_INPUT,
                'required' => System::REQUIRED_YES,
            ],
            [
                'label' => '网址',
                'key' => 'url',
                'genre' => System::GENRE_URL,
                'required' => System::REQUIRED_YES,
            ],
            [
                'label' => '版权',
                'key' => 'copyright',
                'genre' => System::GENRE_INPUT,
                'required' => System::REQUIRED_YES,
            ],
            [
                'label' => 'ICP',
                'key' => 'icp',
                'genre' => System::GENRE_INPUT,
                'required' => System::REQUIRED_YES,
            ],
            [
                'label' => '公安',
                'key' => 'police',
                'genre' => System::GENRE_INPUT,
                'required' => System::REQUIRED_NO,
            ],
            [
                'label' => '闭站',
                'key' => 'close',
                'genre' => System::GENRE_ENABLE,
                'required' => System::REQUIRED_YES,
            ],
            [
                'label' => '分析',
                'key' => 'analyse',
                'genre' => System::GENRE_TEXTAREA,
                'required' => System::REQUIRED_NO,
            ],
            [
                'label' => '签名',
                'key' => 'signature',
                'genre' => System::GENRE_TEXTAREA,
                'required' => System::REQUIRED_NO,
            ],
        ],
    ];
}